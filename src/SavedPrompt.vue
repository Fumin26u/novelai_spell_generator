<template>
    <div class="main">
        <HeaderComponent></HeaderComponent> 
        <div class="content">
            <section class="preset-list">
                <div class="preset-content">
                    <div v-for="(prompt, index) in savedPromptList" :key="prompt.preset_id" @click="selectedPreset = savedPromptList[index]">
                        <img :src="prompt.thumbnail" :alt="prompt.description">
                        <p>{{ prompt.description }}</p>
                    </div>
                </div>
            </section>
            <section class="preset-detail">
                <ul>
                    <li>
                        <!-- <img :src="prompt.originalImage" :alt="prompt.description"> -->
                    </li>
                    <p>{{ selectedPreset }}</p>
                </ul>
            </section>
        </div>
    </div>
</template>
<script>
import fetchData from './assets/ts/fetchData.ts'
import HeaderComponent from './components/HeaderComponent.vue'
import './assets/scss/style.scss'
import { ref, onMounted } from 'vue'
import axios from 'axios'

export default {
    components: {
        HeaderComponent,
    },
    setup() {
        // 各プリセットに必要情報を追加
        const setIsNsfw = presets => {
            presets.map((preset, index) => {
                savedPromptList.value[index]['nsfw'] = preset.commands.match(/nsfw/) ? true:false
            })
        }

        const setImages = presets => {
            const imgPath = './assets/'
            presets.map((preset, index) => {
                const thumbnailPath = preset.image === null ? imgPath + 'noimage.png' : imgPath + 'thumbnail/' + preset.image
                const originalImagePath = preset.image === null ? imgPath + 'noimage.png' : imgPath + 'original/' + preset.image
                console.log(thumbnailPath === imgPath + 'noimage.png')
                savedPromptList.value[index]['thumbnail'] = thumbnailPath
                savedPromptList.value[index]['originalImage'] = originalImagePath
                // savedPromptList.value[index]['thumbnail'] = require(thumbnailPath)
                // savedPromptList.value[index]['originalImage'] = require(originalImagePath)
            })
        }

        const savedPromptList = ref({})
        savedPromptList.value = fetchData
        // 画面ロード時、APIからログインユーザーの登録プロンプト一覧を取得
        onMounted(async() => {
            const url = './register/api/getPreset.php'
            await axios.get(url)
                .then(response => savedPromptList.value = response.data)
                .catch(error => console.log(error))
        })
        setIsNsfw(savedPromptList.value)
        setImages(savedPromptList.value)

        // 選択されたプリセット
        const selectedPreset = ref({})

        return {
            savedPromptList,
            selectedPreset,
        }
    }
}
</script>
<style lang="scss" scoped>
.content {
    display: flex;
    justify-content: space-between;
}
.preset-list {
    width: 68%;
}
.preset-content {
    border-right: 1px solid #888;
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    flex-wrap: wrap;
    > div {
        cursor: pointer;
        width: 23%;
        border: 1px solid #888;
        border-radius: 8px;
        margin: 1em auto;
        > img {
            max-width: 100%;
            height: 140px;
            object-fit: cover;
            border-radius: 8px 8px 0 0;
            margin: 0 auto;
            border-bottom: 1px solid #888;
            display: block;
        }
    }
}

.preset-detail {
    width: 32%;
}
</style>