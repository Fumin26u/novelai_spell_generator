<template lang="">
    <section class="preset-detail register">
        <div>
            <div class="submit-area top">
                <p>{{ 'preset_id' in preset ? 'データ編集':'新規追加' }}</p>
                <div>
                    <input type="checkbox" v-model="isSeniorMode" id="senior-mode">
                    <label for="senior-mode">上級者向け設定あり</label>
                    <button class="btn-common red" @click="deletePreset()">削除</button>
                    <button class="btn-common blue" @click="savePreset()">保存</button>
                </div>
            </div>
            <div class="title-area">
                <h3>タイトル(プロンプトの説明)</h3>
                <input type="text" v-model="preset.description">
            </div>
            <ul class="data-list">
                <li class="image">
                    <h3>画像(ドロップ可)</h3>
                    <input 
                        type="file" 
                        accept="image/*"
                        @change="uploadImage" 
                        @drop="dragImage"
                    >
                    <div v-if="previewImagePath !== null && previewImagePath !== ''" class="image-preview">
                        <button v-if="!isDisplayPreview" @click="isDisplayPreview = true" class="btn-common green">▼プレビューを開く</button>
                        <button v-if="isDisplayPreview" @click="isDisplayPreview = false" class="btn-common red">▲プレビューを閉じる</button>
                        <img v-if="isDisplayPreview" :src="previewImagePath" :alt="preset.description">
                    </div>
                </li>
                <li class="nsfw">
                    <h3>nsfw</h3>
                    <div class="radio-select">
                        <input type="radio" v-model="preset.nsfw" value="A" id="nsfw_a">
                        <label for="nsfw_a">全年齢</label>
                        <input type="radio" v-model="preset.nsfw" value="C" id="nsfw_c">
                        <label for="nsfw_c">R-15</label>
                        <input type="radio" v-model="preset.nsfw" value="Z" id="nsfw_z">
                        <label for="nsfw_z">R-18</label>
                    </div>
                </li>
                <li class="prompt copy">
                    <h3>プロンプト</h3>
                    <input type="text" v-model="preset.commands">
                </li>
                <li class="prompt-ban copy">
                    <h3>BANプロンプト</h3>
                    <input type="text" v-model="preset.commands_ban">
                </li>
                <li class="seed copy">
                    <h3>シード値</h3>
                    <input type="text" v-model="preset.seed">
                </li>
                <li class="resolution">
                    <h3>解像度</h3>
                    <select v-model="preset.resolution">
                        <option v-for="(resolution, index) in resolutionList" :key="index">{{ resolution }}</option>
                    </select>
                </li>
                <section v-if="isSeniorMode">
                    <li>
                        <h3>モデル名</h3>
                        <div class="radio-select">
                            <input type="radio" v-model="preset.model" value="NovelAI" id="model_NovelAI">
                            <label for="model_NovelAI">NovelAI</label>
                            <input type="radio" v-model="preset.model" value="Waifu Diffusion" id="model_Waifu_Diffusion">
                            <label for="model_Waifu_Diffusion">Waifu Diffusion</label>
                            <input type="radio" v-model="preset.model" value="Anything V3" id="model_Anything_V3">
                            <label for="model_Anything_V3">Anything V3</label>
                        </div>
                    </li>
                    <li>
                        <h3>サンプリング回数(Step)</h3>
                        <input type="number" step="1" v-model="preset.sampling">
                    </li>
                    <li>
                        <h3>サンプリングアルゴリズム</h3>
                        <select v-model="preset.sampling_algo">
                            <option v-for="(algorithm, index) in algorithms" :key="index">{{ algorithm }}</option>
                        </select>
                    </li>
                    <li>
                        <h3>Scale値</h3>
                        <input type="number" step="1" v-model="preset.scale">
                    </li>
                    <li>
                        <h3>オプション</h3>
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
                    </li>
                </section>
                <li class="other">
                    <h3>備考</h3>
                    <textarea v-model="preset.others"></textarea>
                </li>
            </ul>
            <button class="btn-common blue submit-bottom" @click="savePreset()">保存</button>
        </div>
    </section>
</template>
<script lang="ts">
import registerPath from '@/assets/ts/registerPath'
import { resolutionList, algorithms } from '@/assets/ts/dbSelectList'
import { ref, watchEffect } from 'vue'
import axios from 'axios'
import '../../assets/scss/savedPrompt.scss'

export default {
    props: {
        selected: {
            type: Object,
        }
    },
    emits: ['setAlertText', 'getPresetData', 'setRegisterMode'],
    setup(props:any, context:any) {
        // 画像プレビューの表示状態
        const isDisplayPreview = ref<boolean>(false)
        
        // DB保存用のデータに画像データを入れると破壊的変更がなされるので対策用の定数
        // Base64文字列に変換した画像
        const base64Image = ref<string | ArrayBuffer | null>('')
        // プレビューする画像
        const previewImagePath = ref<string>(props.selected.originalImage)
        
        // DB保存用のデータ
        // プリセットデータを監視し値が更新された場合DB保存用データを書き換える
        const preset = ref<{[key: string]: any}>({
            image: '',
            from: 'generator',
            commands: '',
            commands_ban: '',
            description: '',
            nsfw: 'A',
            seed: '',
            resolution: 'Portrait (Normal) 512x768',
            others: '',
        })
        watchEffect(() => preset.value = props.selected)

        // 上級者向け設定の表示可否
        const isSeniorMode = ref<boolean>(preset.value.model !== null)

        // プリセットをDBに保存する
        const formUrl = registerPath + 'api/registerPreset.php'
        const savePreset = () => {
            if (preset.value.commands === '') {
                alert('コマンドが入力されていません。')
                return
            }
            if (preset.value.seed !== '' && isNaN(parseInt(preset.value.seed))) {
                alert('Seed値が数値で入力されていません。')
                return
            }

            // 一時的に別定数に保存してある画像データを取り込む
            if (previewImagePath.value !== props.selected.originalImage) {
                preset.value.image = base64Image.value
            }

            const sendData = {...preset.value}
            // 上級者向け設定をOFFにしている場合、該当項目のデータはNULLにする
            if (!isSeniorMode.value) {
                sendData.model = null
                sendData.sampling = null
                sendData.sampling_algo = null
                sendData.scale = null
                sendData.options = null
            } else {
                sendData.options = sendData.options.join(',')
            }
            const formData = JSON.stringify(sendData)
            
            axios.post(formUrl, formData).then(() => {
                context.emit('setAlertText', 'プロンプトをデータベースに登録しました。')
                // 更新できた場合再度データベースからプリセット一覧を取得
                context.emit('getPresetData')
            }).catch(error => {
                context.emit('setAlertText', 'データベース接続に失敗しました。')
                console.log(error)
            })
        }

        // プリセットをDBから削除する
        const deletePreset = () => {
            if (window.confirm('本当に削除しますか?')) {
                axios.post(formUrl, {
                    delete: preset.value.preset_id
                }).then(() => {
                    context.emit('setAlertText', 'プロンプトをデータベースから削除しました。')
                    // 更新できた場合再度データベースからプリセット一覧を取得、編集画面を消去
                    context.emit('getPresetData')
                    context.emit('setRegisterMode', false, 'register')
                }).catch((error) => {
                    context.emit('setAlertText', 'データベース接続に失敗しました。')
                    console.log(error)
                })
            } else {
                return
            }
        }

        // 画像がドラッグ&ドロップされたらファイルをインポートする
        const setImage = (file: Blob) => {
            const reader = new FileReader()
            reader.onloadend = () => {
                base64Image.value = reader.result
                previewImagePath.value = URL.createObjectURL(file)
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

        return {
            preset,
            resolutionList,
            algorithms,
            previewImagePath,
            isDisplayPreview: isDisplayPreview,
            isSeniorMode: isSeniorMode,

            savePreset,
            deletePreset,
            uploadImage,
            dragImage,
        }
    }
}
</script>