<template>
    <div class="main">
        <HeaderComponent></HeaderComponent> 
        <div class="content">
            <section class="preset-list">
                <div class="preset-content">
                    <div v-for="(prompt, index) in savedPromptList" :key="prompt.preset_id" @click="selectedPreset = savedPromptList[index]">
                        <img 
                            :src="[prompt.image === null ? './images/preset/noimage.png' : './images/preset/thumbnail/' + prompt.image]"
                            :alt="prompt.description"
                        >
                        <p>{{ prompt.description }}</p>
                    </div>
                </div>
            </section>
            <section class="preset-detail">
                <div>
                    <p>{{ selectedPreset }}</p>
                </div>
            </section>
        </div>
    </div>
</template>
<script>
import fetchData from './fetchData.js'
import HeaderComponent from './components/HeaderComponent.vue'
import './assets/style.scss'
import { ref, onMounted } from 'vue'
import axios from 'axios'

export default {
    components: {
        HeaderComponent,
    },
    setup() {
        // プリセットのプロンプト内にnsfwが存在するかどうかを調べオブジェクトに追加
        const setIsNsfw = presets => {
            presets.map((preset, index) => {
                presets[index]['nsfw'] = preset.commands.match(/nsfw/) ? true:false
            })
        }

        const savedPromptList = ref({})
        // 画面ロード時、APIからログインユーザーの登録プロンプト一覧を取得
        onMounted(async() => {
            const url = './register/api/getPreset.php'
            await axios.get(url)
                // .then(response => convertJsonToPromptList(response.data))
                .catch(error => console.log(error))
        })
        savedPromptList.value = fetchData
        setIsNsfw(savedPromptList.value)

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