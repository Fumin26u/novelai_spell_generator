<script setup lang="ts">
import apiPath from '@/assets/ts/apiPath'
import HeaderComponent from '@/components/HeaderComponent.vue'
import SearchBoxComponent from '@/components/saver/SearchBoxComponent.vue'
import SelectedPresetComponent from '@/components/saver/SelectedPresetComponent.vue'
import ManagePresetComponent from '@/components/ManagePresetComponent.vue'
import ApiManager from '@/components/api/apiManager'
import {
    Preset,
    PresetDetail,
    SearchData,
    NsfwDisplay,
} from '@/assets/ts/Interfaces/Index'
import '@/assets/scss/savedPrompt.scss'
import { ref, onMounted } from 'vue'

// DBで文字列で保管されているオプションと解像度を表示できるように変更
const revertOptionsToArray = (preset: Preset): string[] => {
    return preset.options !== null && preset.options !== ''
        ? (preset.options = preset.options.split(','))
        : []
}

// 各プリセットに対応する画像とサムネイルのURLを取得
const imgPath = apiPath + 'images/preset/'
const getThumbnailPath = (preset: Preset): string => {
    return preset.image === null || preset.image === ''
        ? imgPath + 'noimage.png'
        : imgPath + 'thumbnail/' + preset.image
}
const getImagePath = (preset: Preset): string => {
    return preset.image === null || preset.image === ''
        ? imgPath + 'noimage.png'
        : imgPath + 'original/' + preset.image
}

// 各プリセットがnsfwかどうか判定
const getNsfwDisplay = (preset: Preset): NsfwDisplay => {
    switch (preset.nsfw) {
        case 'A':
            return '全年齢'
        case 'C':
            return 'R-15'
        case 'Z':
            return 'R-18'
        default:
            return '全年齢'
    }
}

// ログインユーザーの登録プリセット一覧
const savedPresetList = ref<PresetDetail[]>([])
// プリセット一覧から選択されたプリセットを読み込む
const presetInitialData: PresetDetail = {
    index: 0,
    thumbnail: '',
    nsfw_display: '全年齢',
    preset_id: -1,
    image: '',
    imagePath: '',
    commands: '',
    commands_ban: '',
    description: '',
    nsfw: 'A',
    seed: '',
    resolution_width: 512,
    resolution_height: 768,
    resolution: '',
    model: 'NovelAI',
    sampling: 28,
    sampling_algo: 'Euler a',
    scale: 11,
    options: ['Highres. Fix'],
    others: '',
}
// 選択されたプリセットデータ
const selectedPreset = ref<PresetDetail>(presetInitialData)
const selectedPresetIndex = ref<number>(-1)
const selectPreset = (selectPresetIndex: number) => {
    if (selectPresetIndex === -1) {
        selectedPreset.value = { ...presetInitialData }
        selectedPreset.value.index = savedPresetList.value.length
    } else {
        selectedPreset.value = { ...savedPresetList.value[selectPresetIndex] }
        selectedPreset.value.index = selectPresetIndex
    }
    selectedPresetIndex.value = selectPresetIndex
}

// 検索ボックスの入力内容
const searchData = ref<SearchData>({
    method: 'search',
    age: ['A'],
    item: ['description', 'commands'],
    word: '',
    sort: 'created_at',
    order: 'asc',
})

// プリセット検索APIを呼び出し、検索ボックスの内容に応じた値を取得
const apiManager = new ApiManager()
const getPresetData = async (postData: SearchData = searchData.value) => {
    // プリセットを初期化
    savedPresetList.value = []
    
    const url = apiPath + 'managePreset.php'
    const response = await apiManager.get(url, postData)
    if (!response.error) return response.content
}

// APIで取得したプリセット一覧に表示用のデータを挿入する
const createPresetData = (presets: Preset[]) => {
    const PresetDetail = presets.map((preset, i) => {
        const resolutionList =
            preset.resolution !== null && preset.resolution !== ''
                ? preset.resolution.split('x')
                : ['', '']
        preset.options = revertOptionsToArray(preset)
        preset.imagePath = getImagePath(preset)
        preset.resolution_width = resolutionList[0]
        preset.resolution_height = resolutionList[1]

        return {
            ...preset,
            index: i,
            thumbnail: getThumbnailPath(preset),
            nsfw_display: getNsfwDisplay(preset),
        }
    })
    return PresetDetail
}

// 画面ロード時に表示用のプリセットデータを作成する
onMounted(async () => {
    document.title = 'NovelAI プロンプトセーバー'
    savedPresetList.value = createPresetData(await getPresetData())
})

// データ登録・編集モードの状態
const isRegisterMode = ref<boolean>(true)
const setRegisterMode = (state: boolean, mode = '') => {
    // 新規登録の場合は選択されているプリセット詳細データを初期化
    if (state && mode === 'register') selectPreset(-1)
    isRegisterMode.value = state
}

// 検索ボックスの表示有無
const isDisplaySearchBox = ref<boolean>(false)

// コピーした際のアラートを設定
const alertText = ref<string>('')
const setAlertText = (text: string) => (alertText.value = text)

// ページ遷移用のURI
// テストサーバーも含める為パス名を取得して結合
const originPath = new URL(location.href).origin + location.pathname

// ログインユーザーIDを取得
const user_id = ref<string>('')
const getUserInfo = (userId: string) => (user_id.value = userId)
</script>

<template>
    <HeaderComponent @getUserInfo="getUserInfo" />
    <main class="saved-prompt">
        <div class="preset-info not-login" v-if="user_id === ''">
            <p>
                プロンプトセーバーを利用する場合はユーザーログインが必要です。
            </p>
            <a :href="originPath + '#/login'">ログイン</a>
            <a :href="originPath + '#/register'">ユーザー登録</a>
        </div>
        <div class="preset-info" v-else>
            <section class="search-area">
                <div class="title">
                    <div class="display-form">
                        <h2>検索フォーム</h2>
                        <button
                            class="btn-common green"
                            @click="isDisplaySearchBox = true"
                            :style="[
                                !isDisplaySearchBox
                                    ? 'display: inline-block'
                                    : 'display: none',
                            ]"
                        >
                            ▽開く
                        </button>
                        <button
                            class="btn-common red"
                            @click="isDisplaySearchBox = false"
                            :style="[
                                isDisplaySearchBox
                                    ? 'display: inline-block'
                                    : 'display: none',
                            ]"
                        >
                            △閉じる
                        </button>
                    </div>
                    <button
                        @click="setRegisterMode(true, 'register')"
                        class="btn-common green register-preset"
                    >
                        ＋新規追加
                    </button>
                </div>
                <searchBoxComponent
                    v-if="isDisplaySearchBox"
                    :searchBoxData="searchData"
                    @getPresetData="getPresetData"
                />
            </section>
            <section class="preset-message">
                <p class="data-count">
                    {{
                        savedPresetList.length > 0
                            ? savedPresetList.length +
                              '件のデータが存在します。'
                            : '該当のデータが存在しません。'
                    }}
                </p>
                <p class="copy-alert">{{ alertText }}</p>
            </section>
            <section class="preset-list">
                <div class="preset-content">
                    <div
                        v-for="(prompt, index) in savedPresetList"
                        :key="prompt.preset_id !== null ? prompt.preset_id : 0"
                        :class="[
                            selectedPresetIndex === index ? 'selected' : '',
                        ]"
                        @click="selectPreset(index), setRegisterMode(false)"
                    >
                        <img
                            :src="prompt.thumbnail"
                            :alt="
                                prompt.description !== null
                                    ? prompt.description
                                    : ''
                            "
                        />
                        <p>{{ prompt.description }}</p>
                    </div>
                    <div
                        :class="[
                            'register',
                            selectedPresetIndex === -1 ? 'selected' : '',
                        ]"
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
