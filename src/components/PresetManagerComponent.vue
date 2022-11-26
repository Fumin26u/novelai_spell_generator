<template>
    <section class="preset-register">
        <div class="modal-cover" @click="updateModal(false)"></div>
        <div class="db-form-window">
            <div class="generator-title-area">
                <h3>データをDBに登録</h3>
                <small>※<a href="https://nai-pg.com/register/login.php" target="_blank" :style="'font-weight: bold;'">プロンプトセーバー</a>でのログインが必要です。</small>
                <div class="senior-mode-setting">
                    <input type="checkbox" v-model="isSeniorMode" id="senior-mode">
                    <label for="senior-mode">上級者向け設定</label>
                </div>
            </div>
            <div class="saver-title-area">
                <p>{{ 'preset_id' in preset ? 'データ編集':'新規追加' }}</p>
                <div>
                    <input type="checkbox" v-model="isSeniorMode" id="senior-mode">
                    <label for="senior-mode">上級者向け設定</label>
                    <button class="btn-common red" @click="registerPreset('delete')">削除</button>
                    <button class="btn-common blue" @click="registerPreset()">保存</button>
                </div>
            </div>
            <div class="close-modal">
                <span @click="updateModal(false)" class="btn-close"></span>
            </div>
            <dl class="db-form">
                <div class="description">
                    <dt>説明</dt>
                    <dd><input type="text" v-model="preset.description"></dd>
                </div>
                <div class="image">
                    <dt>画像</dt>
                    <dd>
                        <input 
                        type="file" 
                        accept="image/*"
                        @change="uploadImage" 
                        @drop="dragImage"
                        >
                        <button v-if="!isDisplayPreview" @click="isDisplayPreview = true" class="btn-common green">▼プレビューを開く</button>
                        <button v-if="isDisplayPreview" @click="isDisplayPreview = false" class="btn-common red">▲プレビューを閉じる</button>
                        <div v-if="preset.imagePath !== null && preset.imagePath !== ''" class="image-preview">
                            <img v-if="isDisplayPreview" :src="preset.imagePath" :alt="preset.description">
                        </div>
                    </dd>
                </div>
                <div class="prompt">
                    <dt>プロンプト</dt>
                    <dd><input type="text" v-model="preset.commands"></dd>
                </div>
                <div class="prompt_ban">
                    <dt>BANプロンプト</dt>
                    <dd><input type="text" v-model="preset.commands_ban"></dd>
                </div>
                <div class="nsfw">
                    <dt>年齢制限</dt>
                    <dd>
                        <input type="radio" v-model="preset.nsfw" value="A" id="nsfw_a">
                        <label for="nsfw_a">全年齢</label>
                        <input type="radio" v-model="preset.nsfw" value="C" id="nsfw_c">
                        <label for="nsfw_c">R-15</label>
                        <input type="radio" v-model="preset.nsfw" value="Z" id="nsfw_z">
                        <label for="nsfw_z">R-18</label>
                    </dd>
                </div>
                <div class="seed">
                    <dt>シード値</dt>
                    <dd><input type="text" v-model="preset.seed"></dd>
                </div>
                <div class="resolution">
                    <dt>解像度(px)</dt>
                    <dd>
                        <input type="number" v-model="preset.resolution_width" step="64" min="64" max="2048">
                        <span> X </span>
                        <input type="number" v-model="preset.resolution_height" step="64" min="64" max="2048">
                    </dd>
                </div>
                <section v-if="isSeniorMode" class="senior-settings">
                    <div class="model">
                        <dt>モデル名</dt>
                        <dd>
                            <input type="radio" v-model="preset.model" value="NovelAI" id="model_NovelAI">
                            <label for="model_NovelAI">NovelAI</label>
                            <input type="radio" v-model="preset.model" value="Waifu Diffusion" id="model_Waifu_Diffusion">
                            <label for="model_Waifu_Diffusion">Waifu Diffusion</label>
                            <input type="radio" v-model="preset.model" value="Anything V3" id="model_Anything_V3">
                            <label for="model_Anything_V3">Anything V3</label>
                        </dd>
                    </div>
                    <div class="sampling">
                        <dt>サンプリング回数<br>(Step)</dt>
                        <dd><input type="number" step="1" v-model="preset.sampling"></dd>
                    </div>
                    <div class="sampling_algo">
                        <dt>サンプリング<br>アルゴリズム</dt>
                        <dd>
                            <select v-model="preset.sampling_algo">
                                <option v-for="(algorithm, index) in algorithms" :key="index">{{ algorithm }}</option>
                            </select>
                        </dd>
                    </div>
                    <div class="scale">
                        <dt>Scale値</dt>
                        <dd><input type="number" step="1" v-model="preset.scale"></dd>
                    </div>
                    <div class="options">
                        <dt>オプション</dt>
                        <dd>
                            <div>
                                <input type="checkbox" v-model="preset.options" value="Restore Faces" id="Restore_Faces">
                                <label for="Restore_Faces">Restore Faces(顔修復)</label>
                            </div>
                            <div>
                                <input type="checkbox" v-model="preset.options" value="Tiling" id="Tiling">
                                <label for="Tiling">Tiling(テクスチャ生成)</label>
                            </div>
                            <div>
                                <input type="checkbox" v-model="preset.options" value="Highres. Fix" id="Highres_Fix">
                                <label for="Highres_Fix">Highres. Fix(高解像度修正)</label>
                            </div>
                        </dd>
                    </div>
                </section>
                <div class="others">
                    <dt>その他</dt>
                    <dd><textarea v-model="preset.others"></textarea></dd>
                </div>
                <button @click="registerPreset()" class="btn-common blue">保存</button>
            </dl>
        </div>
    </section>
</template>
<script lang="ts">
import registerPath from '@/assets/ts/registerPath'
import algorithms from '@/assets/ts/algorithms'
import { ref, watchEffect } from 'vue'
import axios from 'axios'
import '@/assets/scss/presetManager.scss'

export default {
    emits: [
        'updateModal',
        'selectPreset',
        'setAlertText', 
        'getPresetData', 
        'setRegisterMode'
    ],
    props: {
        prompts: String,
        selectedPreset: Object,
        copyMessage: String,
        displayModalState: Boolean,
    },
    setup(props: any, context: any) {
        // 現在のPathからツール名称を作成
        const currentPath = location.href.slice(-2) === '#/' ? 'generator' : 'saver'
        // DB保存モーダルの表示可否
        const isOpenSaveModal = ref<boolean>(props.displayModalState)
        // Base64文字列に変換した画像
        const base64Image = ref<string | ArrayBuffer | null>('')
        // DB保存用のデータ
        const preset = ref<{[key: string]: any}>({
            image: '',
            imagePath: '',
            from: 'generator',
            commands: '',
            commands_ban: '',
            description: '',
            nsfw: 'A',
            seed: '',
            resolution_width: 512,
            resolution_height: 768,
            model: 'NovelAI',
            sampling: 28,
            sampling_algo: 'Euler a',
            scale: 11,
            options: ['Highres. Fix'],
            others: '',
        })
        // プリセットの内容が更新されたかどうかを監視する
        watchEffect(() => {
            if (currentPath === 'generator') {
                preset.value.commands = props.prompts
            } else if (currentPath === 'saver') {
                preset.value = props.selectedPreset
            }
        })
        
        // 上級者向け設定の表示可否
        const isSeniorMode = ref<boolean>(preset.value.model !== null)
        
        // ジェネレーターのモーダル表示状態更新処理
        const updateModal = (isDisplay: boolean) => {
            if (currentPath === 'generator') {
                context.emit('updateModal', isDisplay)
            }
        }
                
        // プリセットをDBに保存する
        const formUrl = registerPath + 'api/registerPreset.php'
        const registerPreset = (method: string = 'save') => {
            // 削除ボタンが押された場合、確認アラート表示後データ消去命令をAPIに送る
            if (currentPath === 'saver' && method === 'delete') {
                if (confirm('本当に削除しますか？')) {
                    axios.post(formUrl, {
                        delete: preset.value.preset_id
                    }).then(() => {
                        alert('プロンプトをデータベースから削除しました。')
                        // 更新できた場合再度データベースからプリセット一覧を取得、編集画面を消去
                        context.emit('getPresetData')
                        context.emit('setRegisterMode', false)
                    }).catch((error) => {
                        alert('データベース接続に失敗しました。')
                        console.log(error)
                    })
                    return
                } else {
                    return
                }
            }

            if (preset.value.commands === '') {
                alert('プロンプトが入力されていません。')
                return
            }
            if (preset.value.seed !== '' && isNaN(parseInt(preset.value.seed))) {
                alert('Seed値が数値で入力されていません。')
                return
            }

            const sendData = {...preset.value}
            // 解像度を結合して文字列に変更
            sendData.resolution = preset.value.resolution_width + 'x' + preset.value.resolution_height
            // 画像をアップロードしている場合、Base64文字列に変換したものを画像データに上書き
            if (base64Image.value !== '') sendData.image = base64Image.value
            
            // 上級者向け設定をOFFにしている場合、該当項目のデータはNULLにする
            if (!isSeniorMode.value) {
                sendData.model = null
                sendData.sampling = null
                sendData.sampling_algo = null
                sendData.scale = null
                sendData.options = null
            } else {
                // オプションが設定されている場合はカンマ区切りで文字列に変更
                sendData.options = sendData.options.join(',')
            }
            const formData = JSON.stringify(sendData)
            
            axios.post(formUrl, formData).then(() => {
                alert('プロンプトをデータベースに登録しました。')
                if (currentPath === 'generator') {
                    updateModal(false)
                } else if (currentPath === 'saver') {
                    context.emit('getPresetData')
                    context.emit('setRegisterMode', false)
                }
            }).catch(error => {
                alert('データベース接続に失敗しました。')
                console.log(error)
            })
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
            if (event.dataTransfer instanceof HTMLInputElement && event.dataTransfer.files) {
                setImage(event.dataTransfer.files[0])
            }
        }

        // 画像プレビューの表示状態
        const isDisplayPreview = ref<boolean>(false)

        return {
            preset,
            isOpenSaveModal,
            algorithms,
            isSeniorMode: isSeniorMode,
            isDisplayPreview: isDisplayPreview,

            registerPreset,
            updateModal,
            uploadImage,
            dragImage,
        }
    }
}
</script>