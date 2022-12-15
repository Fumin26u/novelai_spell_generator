<script setup lang="ts">
import apiPath from '@/assets/ts/apiPath'
import algorithms from '@/assets/ts/algorithms'
import { Preset, SearchData } from '@/assets/ts/Interfaces/Index'
import { ref, watchEffect } from 'vue'
import ApiManager from '@/components/api/apiManager'
import '@/assets/scss/managePreset.scss'

interface Props {
    prompts?: string
    selectedPreset?: Preset
}
interface Emits {
    (e: 'updateModalState', isDisplay: boolean): boolean
    (e: 'selectPreset', selectPresetIndex: number): void
    (e: 'setAlertText', text: string): string
    (e: 'loadPresetData', postData?: SearchData): Promise<void>
    (e: 'setRegisterMode', state: boolean, mode?: string): void
}

const props = defineProps<Props>()
const emit = defineEmits<Emits>()

// 現在のPathからツール名称を作成
const currentPath = location.href.slice(-2) === '#/' ? 'generator' : 'saver'
// Base64文字列に変換した画像
const base64Image = ref<string | ArrayBuffer | null>('')
// DB保存用のデータ
const presetInitialData: Preset = {
    preset_id: -1,
    image: '',
    imagePath: '',
    commands: '',
    commands_ban: '',
    description: '',
    nsfw: 'A',
    seed: '',
    resolution_width: 512,
    resolution_height: 768,
    resolution: '',
    model: 'NovelAI',
    sampling: 28,
    sampling_algo: 'Euler a',
    scale: 11,
    options: ['Highres. Fix'],
    others: '',
}
const preset = ref<Preset>(presetInitialData)
// プリセットの内容が更新されたかどうかを監視する
watchEffect(() => {
    if (props.prompts !== undefined) {
        preset.value.commands = props.prompts
    } else if (props.selectedPreset !== undefined) {
        preset.value = props.selectedPreset
    }
})

// 上級者向け設定の表示可否
const isSeniorMode = ref<boolean>(preset.value.model !== null)

// ジェネレーターのモーダル表示状態更新処理
const updateModalState = (isDisplay: boolean) => {
    if (currentPath === 'generator') {
        emit('updateModalState', isDisplay)
    }
}

// プリセットをDBに保存する
const formUrl = apiPath + 'managePreset.php'
const apiManager = new ApiManager()
const registerPreset = async (method = 'save') => {
    // 削除ボタンが押された場合、確認アラート表示後データ消去命令をAPIに送る
    if (currentPath === 'saver' && method === 'delete') {
        if (confirm('本当に削除しますか？')) {
            const response = await apiManager.post(formUrl, {
                delete: preset.value.preset_id,
            })

            if (response.error) {
                alert('データベース接続に失敗しました。')
                return
            }

            alert('プロンプトをデータベースから削除しました。')
            // 更新できた場合再度データベースからプリセット一覧を取得、編集画面を消去
            preset.value = presetInitialData
            emit('loadPresetData')
            emit('setRegisterMode', true, 'register')
        }
        return
    }

    if (preset.value.commands === '') {
        alert('プロンプトが入力されていません。')
        return
    }
    if (
        preset.value.seed !== '' &&
        !new RegExp('^[0-9]*$').test(preset.value.seed)
    ) {
        alert('Seed値が数値で入力されていません。')
        return
    }

    const sendData: Preset = { ...preset.value }
    // 解像度を結合して文字列に変更
    sendData.resolution =
        preset.value.resolution_width + 'x' + preset.value.resolution_height
    // 画像をアップロードしている場合、Base64文字列に変換したものを画像データに上書き
    if (base64Image.value !== '') sendData.image = base64Image.value

    // 上級者向け設定をOFFにしている場合、該当項目のデータはNULLにする
    if (!isSeniorMode.value) {
        sendData.model = null
        sendData.sampling = null
        sendData.sampling_algo = null
        sendData.scale = null
        sendData.options = null
    }
    if (Array.isArray(sendData.options)) {
        // オプションが設定されている場合はカンマ区切りで文字列に変更
        sendData.options = sendData.options.join(',')
    }
    const formData = JSON.stringify(sendData)

    const response = await apiManager.post(formUrl, formData)
    if (response.error) {
        alert('データベース接続に失敗しました。')
        return
    }

    alert('プロンプトをデータベースに登録しました。')
    if (currentPath === 'generator') {
        updateModalState(false)
    } 
    if (currentPath === 'saver') {
        preset.value = presetInitialData
        emit('loadPresetData')
        emit('setRegisterMode', false)
    }
}

// 画像がドラッグ&ドロップされたらファイルをインポートする
const setImage = (file: Blob) => {
    const reader = new FileReader()
    reader.onloadend = () => {
        base64Image.value = reader.result
        preset.value.imagePath = URL.createObjectURL(file)
    }
    reader.readAsDataURL(file)
}

const uploadImage = (event: Event) => {
    if (event.target instanceof HTMLInputElement && event.target.files) {
        setImage(event.target.files[0])
    }
}

const dragImage = (event: DragEvent) => {
    if (
        event.dataTransfer instanceof HTMLInputElement &&
        event.dataTransfer.files
    ) {
        setImage(event.dataTransfer.files[0])
    }
}

// 画像プレビューの表示状態
const isDisplayPreview = ref<boolean>(false)
</script>

<template>
    <section class="preset-register">
        <div class="modal-cover" @click="updateModalState(false)"></div>
        <div class="db-form-window">
            <div class="generator-title-area">
                <h3>データをDBに登録</h3>
                <small
                    >※<a
                        href="https://nai-pg.com/register/login.php"
                        target="_blank"
                        :style="'font-weight: bold;'"
                        >プロンプトセーバー</a
                    >でのログインが必要です。</small
                >
                <div class="senior-mode-setting">
                    <input
                        type="checkbox"
                        v-model="isSeniorMode"
                        id="senior-mode"
                    />
                    <label for="senior-mode">上級者向け設定</label>
                </div>
            </div>
            <div class="saver-title-area">
                <p>{{ preset.preset_id !== -1 ? 'データ編集' : '新規追加' }}</p>
                <div>
                    <input
                        type="checkbox"
                        v-model="isSeniorMode"
                        id="senior-mode"
                    />
                    <label for="senior-mode">上級者向け設定</label>
                    <button
                        class="btn-common red"
                        @click="registerPreset('delete')"
                    >
                        削除
                    </button>
                    <button class="btn-common blue" @click="registerPreset()">
                        保存
                    </button>
                </div>
            </div>
            <div class="close-modal">
                <span @click="updateModalState(false)" class="btn-close"></span>
            </div>
            <dl class="db-form">
                <div class="description">
                    <dt>説明</dt>
                    <dd><input type="text" v-model="preset.description" /></dd>
                </div>
                <div class="image">
                    <dt>画像</dt>
                    <dd>
                        <input
                            type="file"
                            accept="image/*"
                            @change="uploadImage"
                            @drop="dragImage"
                        />
                        <button
                            v-if="!isDisplayPreview"
                            @click="isDisplayPreview = true"
                            class="btn-common green"
                        >
                            ▼プレビューを開く
                        </button>
                        <button
                            v-if="isDisplayPreview"
                            @click="isDisplayPreview = false"
                            class="btn-common red"
                        >
                            ▲プレビューを閉じる
                        </button>
                        <div
                            v-if="
                                preset.imagePath !== null &&
                                preset.imagePath !== ''
                            "
                            class="image-preview"
                        >
                            <img
                                v-if="isDisplayPreview"
                                :src="preset.imagePath"
                                :alt="
                                    preset.description !== null
                                        ? preset.description
                                        : ''
                                "
                            />
                        </div>
                    </dd>
                </div>
                <div class="prompt">
                    <dt>プロンプト</dt>
                    <dd><input type="text" v-model="preset.commands" /></dd>
                </div>
                <div class="prompt_ban">
                    <dt>BANプロンプト</dt>
                    <dd><input type="text" v-model="preset.commands_ban" /></dd>
                </div>
                <div class="nsfw">
                    <dt>年齢制限</dt>
                    <dd>
                        <input
                            type="radio"
                            v-model="preset.nsfw"
                            value="A"
                            id="nsfw_a"
                        />
                        <label for="nsfw_a">全年齢</label>
                        <input
                            type="radio"
                            v-model="preset.nsfw"
                            value="C"
                            id="nsfw_c"
                        />
                        <label for="nsfw_c">R-15</label>
                        <input
                            type="radio"
                            v-model="preset.nsfw"
                            value="Z"
                            id="nsfw_z"
                        />
                        <label for="nsfw_z">R-18</label>
                    </dd>
                </div>
                <div class="seed">
                    <dt>シード値</dt>
                    <dd><input type="text" v-model="preset.seed" /></dd>
                </div>
                <div class="resolution">
                    <dt>解像度(px)</dt>
                    <dd>
                        <input
                            type="number"
                            v-model="preset.resolution_width"
                            step="64"
                            min="64"
                            max="2048"
                        />
                        <span> X </span>
                        <input
                            type="number"
                            v-model="preset.resolution_height"
                            step="64"
                            min="64"
                            max="2048"
                        />
                    </dd>
                </div>
                <section v-if="isSeniorMode" class="senior-settings">
                    <div class="model">
                        <dt>モデル名</dt>
                        <dd>
                            <input
                                type="radio"
                                v-model="preset.model"
                                value="NovelAI"
                                id="model_NovelAI"
                            />
                            <label for="model_NovelAI">NovelAI</label>
                            <input
                                type="radio"
                                v-model="preset.model"
                                value="Waifu Diffusion"
                                id="model_Waifu_Diffusion"
                            />
                            <label for="model_Waifu_Diffusion"
                                >Waifu Diffusion</label
                            >
                            <input
                                type="radio"
                                v-model="preset.model"
                                value="Anything V3"
                                id="model_Anything_V3"
                            />
                            <label for="model_Anything_V3">Anything V3</label>
                        </dd>
                    </div>
                    <div class="sampling">
                        <dt>サンプリング回数<br />(Step)</dt>
                        <dd>
                            <input
                                type="number"
                                step="1"
                                v-model="preset.sampling"
                            />
                        </dd>
                    </div>
                    <div class="sampling_algo">
                        <dt>サンプリング<br />アルゴリズム</dt>
                        <dd>
                            <select v-model="preset.sampling_algo">
                                <option
                                    v-for="(algorithm, index) in algorithms"
                                    :key="index"
                                >
                                    {{ algorithm }}
                                </option>
                            </select>
                        </dd>
                    </div>
                    <div class="scale">
                        <dt>Scale値</dt>
                        <dd>
                            <input
                                type="number"
                                step="1"
                                v-model="preset.scale"
                            />
                        </dd>
                    </div>
                    <div class="options">
                        <dt>オプション</dt>
                        <dd>
                            <div>
                                <input
                                    type="checkbox"
                                    v-model="preset.options"
                                    value="Restore Faces"
                                    id="Restore_Faces"
                                />
                                <label for="Restore_Faces"
                                    >Restore Faces(顔修復)</label
                                >
                            </div>
                            <div>
                                <input
                                    type="checkbox"
                                    v-model="preset.options"
                                    value="Tiling"
                                    id="Tiling"
                                />
                                <label for="Tiling"
                                    >Tiling(テクスチャ生成)</label
                                >
                            </div>
                            <div>
                                <input
                                    type="checkbox"
                                    v-model="preset.options"
                                    value="Highres. Fix"
                                    id="Highres_Fix"
                                />
                                <label for="Highres_Fix"
                                    >Highres. Fix(高解像度修正)</label
                                >
                            </div>
                        </dd>
                    </div>
                </section>
                <div class="others">
                    <dt>その他</dt>
                    <dd><textarea v-model="preset.others"></textarea></dd>
                </div>
                <button @click="registerPreset()" class="btn-common blue">
                    保存
                </button>
            </dl>
        </div>
    </section>
</template>
