<template>
    <div class="main">
        <HeaderComponent :user="user_id"></HeaderComponent> 
        <div class="content">
            <section class="search-area">
                <div class="title">
                    <div class="display-form">
                        <h2>検索フォーム</h2>
                        <button 
                            class="btn-common green" 
                            @click="isDisplaySearchBox = true" 
                            :style="[!isDisplaySearchBox ? 'display: inline-block':'display: none']"
                        >▽開く</button>
                        <button 
                            class="btn-common red" 
                            @click="isDisplaySearchBox = false" 
                            :style="[isDisplaySearchBox ? 'display: inline-block':'display: none']"
                        >△閉じる</button>
                    </div>
                    <button @click="setRegisterMode(true, 'register')" class="btn-common green register-preset">＋新規追加</button>
                </div>
                <searchBoxComponent
                    v-if="isDisplaySearchBox"
                    :searchBoxData="searchData"
                    @getPresetData="getPresetData"
                />
            </section>
            <section class="preset-info">
                <p class="data-count">{{ savedPromptList.length > 0 ? savedPromptList.length + '件のデータが存在します。':'該当のデータが存在しません。' }}</p>
                <p class="copy-alert">{{ alertText }}</p>
            </section>
            <section class="preset-list">
                <div class="preset-content">
                    <div 
                        v-for="(prompt, index) in savedPromptList" 
                        :key="prompt.preset_id" 
                        :class="[selectedPresetIndex === index ? 'selected':'']"
                        @click="selectPreset(index), setRegisterMode(false)"
                    >
                        <img :src="prompt.thumbnail" :alt="prompt.description">
                        <p>{{ prompt.description }}</p>
                    </div>
                    <div
                        :class="['register', selectedPresetIndex === -1 ? 'selected':'']"
                        @click="setRegisterMode(true, 'register')"
                    >
                        <span>新規追加</span>
                    </div>
                </div>
            </section>
            <SelectedPresetComponent 
                v-if="!isRegisterMode"
                :selected="selectedPreset"
                @setAlertText="setAlertText"
                @setRegisterMode="setRegisterMode"
            />
            <ManagePresetComponent 
                v-else
                :selected="selectedPreset"
                @setAlertText="setAlertText"
                @getPresetData="getPresetData"
                @setRegisterMode="setRegisterMode"
            />
        </div>
    </div>
</template>
<script lang="ts">
import fetchData from '@/assets/ts/fetchData'
import registerPath from '@/assets/ts/registerPath'
import HeaderComponent from './components/HeaderComponent.vue'
import SearchBoxComponent from './components/saver/SearchBoxComponent.vue'
import SelectedPresetComponent from './components/saver/SelectedPresetComponent.vue'
import ManagePresetComponent from './components/saver/ManagePresetComponent.vue'
import './assets/scss/savedPrompt.scss'
import { ref, onMounted } from 'vue'
import axios from 'axios'

export default {
    components: {
        HeaderComponent,
        SearchBoxComponent,
        SelectedPresetComponent,
        ManagePresetComponent,
    },
    setup() {
        // ログインユーザーの登録プリセット一覧
        const savedPromptList = ref<any>([])
        
        // 検索ボックスの表示有無
        const isDisplaySearchBox = ref<boolean>(false)
        
        // 各プリセットに対応する画像とサムネイルのURLを取得
        const setImages = (presets: {[key: string]: any}[]) => {
            const imgPath = registerPath + 'images/preset/'
            presets.map((preset, index) => {
                const thumbnailPath = preset.image === null ? imgPath + 'noimage.png' : imgPath + 'thumbnail/' + preset.image
                const originalImagePath = preset.image === null ? imgPath + 'noimage.png' : imgPath + 'original/' + preset.image
                savedPromptList.value[index]['thumbnail'] = thumbnailPath
                savedPromptList.value[index]['originalImage'] = originalImagePath
            })
        }

        // 各プリセットがnsfwかどうか判定
        const setIsNsfw = (presets: {[key: string]: any}[]) => {
            presets.map((_, index) => {
                switch (savedPromptList.value[index].nsfw) {
                    case 'A':
                        savedPromptList.value[index]['nsfw_display'] = '全年齢'
                        break
                    case 'C':
                        savedPromptList.value[index]['nsfw_display'] = 'R-15'
                        break
                    case 'Z':
                        savedPromptList.value[index]['nsfw_display'] = 'R-18'
                        break
                }
            })
        }
        
        // プリセット一覧から選択されたプリセットを読み込む
        const selectedPreset = ref<object>({})
        const selectedPresetIndex = ref<number>(-1)
        const selectPreset = (index: number) => {
            selectedPreset.value = index === -1 ? {} : selectedPreset.value = savedPromptList.value[index]
            selectedPresetIndex.value = index
        }

        // データ登録・編集モードの状態
        const isRegisterMode = ref<boolean>(true)
        const setRegisterMode = (state: boolean, mode: string = '') => {
            // 新規登録の場合は選択されているプリセット詳細データを初期化
            if (state && mode === 'register') selectPreset(-1)

            isRegisterMode.value = state
        }

        // 検索ボックスの入力内容
        const searchData = ref<object>({
            age: ['A'],
            item: ['description', 'commands'],
            word: '',
            sort: 'created_at',
            order: 'asc'
        })
       
        // プリセット検索APIを呼び出し、検索ボックスの内容に応じた値を取得
        const getPresetData = async(postData: {[key: string]: any} = searchData.value) => {
            const url = registerPath +  'api/getPreset.php'
            // プリセットを初期化
            savedPromptList.value = []
            await axios.get(url, {
                params: postData
            }).then(response => {
                    if (response.data !== '') {
                        savedPromptList.value = response.data
                        setImages(savedPromptList.value)
                        setIsNsfw(savedPromptList.value)
                    }
                })
                .catch(error => {
                    savedPromptList.value = fetchData
                    setImages(savedPromptList.value)
                    setIsNsfw(savedPromptList.value)
                    console.log(error) 
                })
        }

        // ログインユーザーIDを取得
        const user_id = ref<string>('')
        const getUserInfo = async() => {
            const url = registerPath + 'api/getUserInfo.php'
            axios.get(url)
                .then(response => user_id.value = response.data.user_id)
                .catch(error => console.log(error))
        }

        // コピーした際のアラートを設定
        const alertText = ref<string>('')
        const setAlertText = (text: string) => alertText.value = text

        // 画面ロード時、APIからログインユーザーの登録プロンプト一覧を取得
        onMounted(() => {
            getUserInfo()
            getPresetData()
        })

        return {
            savedPromptList,
            isDisplaySearchBox: isDisplaySearchBox,
            selectedPreset,
            selectedPresetIndex,
            searchData: searchData,
            alertText,
            isRegisterMode,
            user_id,

            selectPreset,
            getPresetData,
            setAlertText,
            setRegisterMode,
        }
    }
}
</script>