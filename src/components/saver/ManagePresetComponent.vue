<template lang="">
    <section class="preset-detail register">
        <div>
            <div class="title-area">
                <h3>タイトル(プロンプトの説明)</h3>
                <input type="text" v-model="promptForDB.description">
                <!-- <button class="btn-common blue">編集</button>
                <button class="btn-common green" :style="'display:none;'">保存</button> -->
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
                        <img v-if="isDisplayPreview" :src="previewImagePath" :alt="promptForDB.description">
                    </div>
                </li>
                <li class="nsfw">
                    <h3>nsfw</h3>
                    <div class="radio-select">
                        <input type="radio" v-model="promptForDB.nsfw" value="A" id="nsfw_a">
                        <label for="nsfw_a">全年齢</label>
                        <input type="radio" v-model="promptForDB.nsfw" value="C" id="nsfw_c">
                        <label for="nsfw_c">R-15</label>
                        <input type="radio" v-model="promptForDB.nsfw" value="Z" id="nsfw_z">
                        <label for="nsfw_z">R-18</label>
                    </div>
                </li>
                <li class="prompt copy">
                    <h3>プロンプト</h3>
                    <input type="text" v-model="promptForDB.commands">
                </li>
                <li class="prompt-ban copy">
                    <h3>BANプロンプト</h3>
                    <input type="text" v-model="promptForDB.commands_ban">
                </li>
                <li class="seed copy">
                    <h3>シード値</h3>
                    <input type="text" v-model="promptForDB.seed">
                </li>
                <li class="resolution">
                    <h3>解像度</h3>
                    <select v-model="promptForDB.resolution">
                        <option v-for="(resolution, index) in resolutionList" :key="index">{{ resolution }}</option>
                    </select>
                </li>
                <li class="other">
                    <h3>備考</h3>
                    <textarea v-model="promptForDB.others"></textarea>
                </li>
            </ul>
        </div>
    </section>
</template>
<script lang="ts">
import { ref } from 'vue'
import '../../assets/scss/savedPrompt.scss'

export default {
    props: {
        selected: {
            type: Object,
        }
    },
    emits: [],
    setup(props:any) {
        // 画像プレビューの表示状態
        const isDisplayPreview = ref<boolean>(false)
        
        // DB保存用のデータに画像データを入れると破壊的変更がなされるので対策用の定数
        // Base64文字列に変換した画像
        const base64Image = ref<string | ArrayBuffer | null>('')
        // プレビューする画像
        const previewImagePath = ref<string>(props.selected.originalImage)
        
        // DB保存用のデータ
        // プリセットデータを監視し値が更新された場合DB保存用データを書き換える
        const promptForDB = ref<{[key: string]: any}>(props.selected)

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
            promptForDB,
            previewImagePath,
            resolutionList: [
                'Portrait (Normal) 512x768',
                'LandScape (Normal) 768x512',
                'Square (Normal) 640x640',
                'Portrait (Small) 384x640',
                'LandScape (Small) 640x384',
                'Square (Small) 512x512',
                'Portrait (Large) 512x1024',
                'LandScape (Large) 1024x512',
                'Square (Large) 1024x1024',
            ],
            isDisplayPreview: isDisplayPreview,

            uploadImage,
            dragImage,
        }
    }
}
</script>