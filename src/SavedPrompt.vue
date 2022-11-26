<template>
    <HeaderComponent :user="user_id"></HeaderComponent> 
    <main class="content">
        <div class="preset-info not-login" v-if="user_id === ''">
            <p>プロンプトセーバーを利用する場合はユーザーログインが必要です。</p>
            <a href="./register/login.php">ログイン</a>
            <a href="./register/register.php">ユーザー登録</a>
        </div>
        <div class="preset-info" v-else>
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
            <section class="preset-message">
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
            <ModalDBComponent 
                id="saver"
                v-else
                :selectedPreset="selectedPreset"
                @selectPreset="selectPreset"
                @setAlertText="setAlertText"
                @getPresetData="getPresetData"
                @setRegisterMode="setRegisterMode"
            />
        </div>
    </main>
</template>
<script lang="ts">
import registerPath from '@/assets/ts/registerPath'
import HeaderComponent from './components/HeaderComponent.vue'
import SearchBoxComponent from './components/saver/SearchBoxComponent.vue'
import SelectedPresetComponent from './components/saver/SelectedPresetComponent.vue'
import ModalDBComponent from './components/ModalDBComponent.vue'
import './assets/scss/savedPrompt.scss'
import { ref, onMounted } from 'vue'
import axios from 'axios'

export default {
    components: {
        HeaderComponent,
        SearchBoxComponent,
        SelectedPresetComponent,
        ModalDBComponent,
    },
    setup() {
        // ログインユーザーの登録プリセット一覧
        const savedPromptList = ref<any>([])
        
        // 検索ボックスの表示有無
        const isDisplaySearchBox = ref<boolean>(false)

        // DBで文字列で保管されているオプションと解像度を表示できるように変更
        const revertDBData = (presets: {[key: string]: any}[]) => {
            presets.map((preset, index) => {
                if (preset.options !== null && preset.options !== '') {
                    savedPromptList.value[index].options = preset.options.split(',')
                } else {
                    savedPromptList.value[index].options = []
                }

                if (preset.resolution !== null && preset.resolution !== '') {
                    const resolutionList = preset.resolution.split('x')
                    savedPromptList.value[index]['resolution_width'] = resolutionList[0]
                    savedPromptList.value[index]['resolution_height'] = resolutionList[1]
                }
            })
        }
        
        // 各プリセットに対応する画像とサムネイルのURLを取得
        const setImages = (presets: {[key: string]: any}[]) => {
            const imgPath = registerPath + 'images/preset/'
            presets.map((preset, index) => {
                const thumbnailPath = preset.image === null ? imgPath + 'noimage.png' : imgPath + 'thumbnail/' + preset.image
                const originalImagePath = preset.image === null ? imgPath + 'noimage.png' : imgPath + 'original/' + preset.image
                savedPromptList.value[index]['thumbnail'] = thumbnailPath
                savedPromptList.value[index]['imagePath'] = originalImagePath
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
        const presetInitialData = {
            index: 0,
            image: '',
            from: 'generator',
            commands: '',
            commands_ban: '',
            description: '',
            nsfw: 'A',
            seed: '',
            resolution: 'Portrait (Normal) 512x768',
            model: 'NovelAI',
            sampling: 28,
            sampling_algo: 'Euler a',
            scale: 11,
            options: ['Highres. Fix'],
            others: '',
        }
        // 選択されたプリセットデータ
        const selectedPreset = ref<{[key:string]: string | string[] | number | null}>(presetInitialData)
        const selectedPresetIndex = ref<number>(-1)
        const selectPreset = (selectPresetIndex: number) => {
            if (selectPresetIndex === -1) {
                selectedPreset.value = {...presetInitialData}
                selectedPreset.value.index = savedPromptList.value.length
            } else {
                selectedPreset.value = {...savedPromptList.value[selectPresetIndex]}
                selectedPreset.value.index = selectPresetIndex
            }
            selectedPresetIndex.value = selectPresetIndex
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
                        revertDBData(savedPromptList.value)
                        setImages(savedPromptList.value)
                        setIsNsfw(savedPromptList.value)
                    }
                })
                .catch(error => {
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
            document.title = 'NovelAI プロンプトセーバー'
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