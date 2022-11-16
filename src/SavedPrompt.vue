<template>
    <div class="main">
        <HeaderComponent></HeaderComponent> 
        <div class="content">
            <searchBoxComponent
                :age="selectAge"
                :target="searchTarget"
                :word="searchWord"
                :sort="sortTarget"
                :order="sortOrder"
                @getPresetData="getPresetData"
            />
            <section class="preset-list">
                <div class="preset-content">
                    <div v-for="(prompt, index) in savedPromptList" :key="prompt.preset_id" @click="selectPreset(index)">
                        <img :src="prompt.thumbnail" :alt="prompt.description">
                        <p>{{ prompt.description }}</p>
                    </div>
                </div>
            </section>
            <section class="preset-detail">
                <div v-if="selectedPreset !== null">
                    <div class="title-area">
                        <h2>{{ selectedPreset.description }}</h2>
                        <button class="btn-common blue">編集</button>
                        <button class="btn-common add" :style="'display:none;'">保存</button>
                    </div>
                    <ul class="data-list">
                        <li class="image">
                            <img :src="selectedPreset.originalImage" alt="">
                        </li>
                        <li class="nsfw">
                            <h3>nsfw</h3>
                            <p>{{ selectedPreset.nsfw ? 'あり' : 'なし' }}</p>
                        </li>
                        <li class="prompt copy">
                            <h3>プロンプト</h3>
                            <button class="btn-common blue"></button>
                            <p>{{ selectedPreset.commands }}</p>
                        </li>
                        <li class="prompt-ban copy">
                            <h3>BANプロンプト</h3>
                            <button class="btn-common blue"></button>
                            <p>{{ selectedPreset.commands_ban }}</p>
                        </li>
                        <li class="seed copy">
                            <h3>シード値</h3>
                            <p>{{ selectedPreset.seed }}</p>
                        </li>
                        <li class="resolution">
                            <h3>解像度</h3>
                            <p>{{ selectedPreset.resolution }}</p>
                        </li>
                        <li class="other">
                            <h3>備考</h3>
                            <p>{{ selectedPreset.others }}</p>
                        </li>
                    </ul>
                </div>
            </section>
        </div>
    </div>
</template>
<script lang="ts">
import fetchData from './assets/ts/fetchData'
import HeaderComponent from './components/HeaderComponent.vue'
import SearchBoxComponent from './components/SearchBoxComponent.vue'
import './assets/scss/savedPrompt.scss'
import { ref, onMounted, watchEffect } from 'vue'
import axios from 'axios'

export default {
    components: {
        HeaderComponent,
        SearchBoxComponent,
    },
    setup() {
        // ログインユーザーの登録プリセット一覧
        const savedPromptList = ref<any>([])
        
        // 各プリセットに対応する画像とサムネイルのURLを取得
        const setImages = (presets: {[key: string]: any}[], currentPath: string) => {
            savedPromptList.value = presets
            const imgPath = currentPath === 'local' ? './images/preset/' : './register/images/preset/'
            presets.map((preset, index) => {
                const thumbnailPath = preset.image === null ? imgPath + 'noimage.png' : imgPath + 'thumbnail/' + preset.image
                const originalImagePath = preset.image === null ? imgPath + 'noimage.png' : imgPath + 'original/' + preset.image
                savedPromptList.value[index]['thumbnail'] = thumbnailPath
                savedPromptList.value[index]['originalImage'] = originalImagePath
            })
        }

        // 各プリセットがnsfwかどうか判定
        const setIsNsfw = (presets: {[key: string]: any}[]) => {
            presets.map((preset, index) => {
                savedPromptList.value[index]['nsfw'] = preset.commands.match(/nsfw/) ? true:false
            })
        }
        
        // プリセット一覧から選択されたプリセットを読み込む
        const selectedPreset = ref<any>(null)
        const selectPreset = (index: number) => selectedPreset.value = savedPromptList.value[index]
        

        // 検索ボックスの入力内容
        const selectAge = ref<string[]>(['noLimit', 'nsfw'])
        const searchTarget = ref<string[]>(['description', 'prompt'])
        const searchWord = ref<string>('')
        const sortTarget = ref<string>('created')
        const sortOrder = ref<string>('asc')
       
        // ページの環境(プリセット取得場所参照に使用)
        const currentPath = ref<string>('local')
        // プリセット検索APIを呼び出し、検索ボックスの内容に応じた値を取得
        const getPresetData = async(postData: {[key: string]: any}) => {
            const url = './register/api/getPreset.php'
            await axios.get(url, postData)
                .then(response => {
                    if (response.data !== '') { 
                        switch (location.origin + location.pathname) {
                            case 'https://fuminsv.sakura.ne.jp/sgtest/':
                                currentPath.value = 'testServer'
                                break
                            case 'https://nai-pg.com/':
                                currentPath.value = 'mainServer'
                                break
                            default:
                                currentPath.value = 'local'
                        }
                        setImages(response.data, currentPath.value)
                        setIsNsfw(response.data)
                    }
                })
                .catch(error => {
                    setImages(fetchData, currentPath.value)
                    setIsNsfw(fetchData)
                    console.log(error) 
                })
        }

        // 画面ロード時、APIからログインユーザーの登録プロンプト一覧を取得
        onMounted(() => {
            const postData = {
                age: selectAge.value,
                search_item: searchTarget.value,
                search_word: searchWord.value,
                sort: sortTarget.value,
                order: sortOrder.value,
            }
            getPresetData(postData)
        })
        // 検索ボックスの入力内容が変化した場合、指定された内容で表示するプリセットを抽出
        watchEffect(() => {
            const postData = {
                age: selectAge.value,
                search_item: searchTarget.value,
                search_word: searchWord.value,
                sort: sortTarget.value,
                order: sortOrder.value,
            } 
            getPresetData(postData)
        })

        return {
            savedPromptList,
            selectedPreset,
            selectPreset,
            getPresetData,

            selectAge: selectAge,
            searchTarget: searchTarget,
            searchWord: searchWord,
            sortTarget: sortTarget,
            sortOrder: sortOrder,
        }
    }
}
</script>