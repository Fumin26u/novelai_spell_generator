<template>
    <div class="main">
        <HeaderComponent :user="user_id"></HeaderComponent> 
        <div class="content">
            <searchBoxComponent
                :searchBoxData="searchData"
                @getPresetData="getPresetData"
            />
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
            />
        </div>
    </div>
</template>
<script lang="ts">
import fetchData from './assets/ts/fetchData'
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
        
        // 各プリセットに対応する画像とサムネイルのURLを取得
        const setImages = (presets: {[key: string]: any}[], currentPath: string) => {
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
        const selectedPreset = ref<any>(null)
        const selectedPresetIndex = ref<number>(-1)
        const selectPreset = (index: number) => {
            selectedPreset.value = savedPromptList.value[index]
            selectedPresetIndex.value = index
            console.log(selectedPreset.value)
        }

        // 検索ボックスの入力内容
        const searchData = ref<object>({
            age: ['A'],
            item: ['description', 'commands'],
            word: '',
            sort: 'created_at',
            order: 'asc'
        })
       
        // ページの環境(プリセット取得場所参照に使用)
        const currentPath = ref<string>('local')
        // プリセット検索APIを呼び出し、検索ボックスの内容に応じた値を取得
        const getPresetData = async(postData: {[key: string]: any}) => {
            const url = './register/api/getPreset.php'
            // プリセットを初期化
            savedPromptList.value = []
            await axios.get(url, {
                params: postData
            }).then(response => {
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
                        savedPromptList.value = response.data
                        setImages(savedPromptList.value, currentPath.value)
                        setIsNsfw(savedPromptList.value)
                    }
                })
                .catch(error => {
                    savedPromptList.value = fetchData
                    setImages(savedPromptList.value, currentPath.value)
                    setIsNsfw(savedPromptList.value)
                    console.log(error) 
                })
        }

        // ログインユーザーIDを取得
        const user_id = ref<string>('')
        const getUserInfo = async() => {
            const url = './register/api/getUserInfo.php'
            axios.get(url)
                .then(response => user_id.value = response.data.user_id)
                .catch(error => console.log(error))
        }

        // コピーした際のアラートを設定
        const alertText = ref<string>('')
        const setAlertText = (text: string) => alertText.value = text

        // データ登録・編集モードの状態
        const isRegisterMode = ref<boolean>(false)
        const setRegisterMode = (state: boolean, mode: string = '') => {
            isRegisterMode.value = state
            // 新規登録の場合は選択されているプリセット詳細データを初期化
            if (!state && mode === 'register') selectedPreset.value = null
        }

        // 画面ロード時、APIからログインユーザーの登録プロンプト一覧を取得
        onMounted(() => {
            getUserInfo()
            getPresetData(searchData.value)
        })

        return {
            savedPromptList,
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