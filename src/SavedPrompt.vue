<template>
    <div class="main">
        <HeaderComponent></HeaderComponent> 
        <div class="content">
            <section class="search-area">
                <div class="title">
                    <h2>検索フォーム</h2>
                    <button 
                        class="btn-common add" 
                        @click="displaySearchBox(true)" 
                        :style="[!isDisplaySearchBox ? 'display: inline-block':'display: none']"
                    >▽開く</button>
                    <button 
                        class="btn-common delete" 
                        @click="displaySearchBox(false)" 
                        :style="[isDisplaySearchBox ? 'display: inline-block':'display: none']"
                    >△閉じる</button>
                </div>
                <div class="search-box" v-if="isDisplaySearchBox">
                    <dl>
                        <div>
                            <dt>年齢制限</dt>
                            <dd>
                                <input type="checkbox" v-model="selectAge" value="allItems" id="allItems">
                                <label for="allItems">全て選択</label>
                                <input type="checkbox" v-model="selectAge" value="noLimit" id="noLimit">
                                <label for="noLimit">全年齢</label>
                                <input type="checkbox" v-model="selectAge" value="nsfw" id="nsfw">
                                <label for="nsfw">R-18</label>
                            </dd>
                        </div>
                        <div>
                            <dt>ワード検索</dt>
                            <dd>
                                <div>
                                    <label>検索項目:</label>
                                    <input type="checkbox" v-model="searchTarget" value="allItems" id="word-allItems">
                                    <label for="word-allItems">全て選択</label>
                                    <input type="checkbox" v-model="searchTarget" value="description" id="description">
                                    <label for="description">タイトル</label>
                                    <input type="checkbox" v-model="searchTarget" value="prompt" id="prompt">
                                    <label for="prompt">プロンプト</label>
                                    <input type="checkbox" v-model="searchTarget" value="prompt_ban" id="prompt_ban">
                                    <label for="prompt_ban">BANプロンプト</label>
                                    <input type="checkbox" v-model="searchTarget" value="seed" id="seed">
                                    <label for="seed">シード値</label>
                                    <input type="checkbox" v-model="searchTarget" value="resolution" id="resolution">
                                    <label for="resolution">解像度</label>
                                    <input type="checkbox" v-model="searchTarget" value="others" id="others">
                                    <label for="others">備考</label>
                                </div>
                                <div>
                                    <label for="search-word">検索ワード:</label>
                                    <input type="text" v-model="searchWord" id="search-word">
                                </div>
                            </dd>
                        </div>
                        <div>
                            <dt>ソート</dt>
                            <dd>
                                <div>
                                    <input type="radio" v-model="sortTarget" value="description" id="sort-description">
                                    <label for="sort-description">説明</label>
                                    <input type="radio" v-model="sortTarget" value="seed" id="sort-seed">
                                    <label for="sort-seed">シード値</label>
                                    <input type="radio" v-model="sortTarget" value="resolution" id="sort-resolution">
                                    <label for="sort-resolution">解像度</label>
                                    <input type="radio" v-model="sortTarget" value="created" id="sort-created">
                                    <label for="sort-created">作成日付</label>
                                    <input type="radio" v-model="sortTarget" value="updated" id="sort-updated">
                                    <label for="sort-updated">更新日付</label>
                                </div>
                                <div>
                                    <input type="radio" v-model="sortOrder" value="asc" id="sort-asc">
                                    <label for="sort-asc">昇順</label>
                                    <input type="radio" v-model="sortOrder" value="desc" id="sort-desc">
                                    <label for="sort-desc">降順</label>
                                </div>
                            </dd>
                        </div>
                    </dl>
                </div>
            </section>
            <section class="preset-list">
                <div class="preset-content">
                    <div v-for="(prompt, index) in savedPromptList" :key="prompt.preset_id" @click="selectedPreset = savedPromptList[index]">
                        <img :src="prompt.thumbnail" :alt="prompt.description">
                        <p>{{ prompt.description }}</p>
                    </div>
                </div>
            </section>
            <section class="preset-detail">
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
            </section>
        </div>
    </div>
</template>
<script lang="ts">
import fetchData from './assets/ts/fetchData'
import HeaderComponent from './components/HeaderComponent.vue'
import './assets/scss/savedPrompt.scss'
import { ref, onMounted, watchEffect } from 'vue'
import axios from 'axios'

export default {
    components: {
        HeaderComponent,
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

        // 選択されたプリセット
        const selectedPreset = ref<any>({})
        // 検索ボックスの表示有無
        const isDisplaySearchBox = ref<boolean>(true)
        const displaySearchBox = (state: boolean) => isDisplaySearchBox.value = state

        // 検索ボックスの入力内容
        const selectAge = ref<string[]>(['allItems'])
        const searchTarget = ref<string[]>(['allItems'])
        const searchWord = ref<string>('')
        const sortTarget = ref<string>('created')
        const sortOrder = ref<string>('asc')
       
        // ページの環境(プリセット取得場所参照に使用)
        const currentPath = ref<string>('local')
        // プリセット検索APIを呼び出し、検索ボックスの内容に応じた値を取得
        const getPresetData = async(postData: any) => {
            const url = './register/api/getPreset.php'
            await axios.post(url, postData)
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
            isDisplaySearchBox: isDisplaySearchBox,
            displaySearchBox,

            selectAge: selectAge,
            searchTarget: searchTarget,
            searchWord: searchWord,
            sortTarget: sortTarget,
            sortOrder: sortOrder,
        }
    }
}
</script>