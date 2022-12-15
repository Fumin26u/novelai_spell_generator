<script setup lang="ts">
import HeaderComponent from '@/components/HeaderComponent.vue'
import SelectedPromptComponent from '@/components/master/SelectedPromptComponent.vue'
import '@/assets/scss/masterData.scss'
import ApiManager from '@/components/api/apiManager'
import user_id from '@/components/api/getUserId'
import { MasterData, MasterPrompt } from '@/assets/ts/Interfaces/Index'
import { promptInitial, genreInitial } from '@/assets/ts/initialValues'
import apiPath from '@/assets/ts/apiPath'
import { ref, onMounted } from 'vue'
import { useRouter } from 'vue-router'

// 自分以外のユーザーがアクセスした場合強制リダイレクト
const router = useRouter()
if (user_id !== 'Fumiya0719') router.push('./')

// DBから取得したマスタデータ一覧
const promptList = ref<MasterData[]>([])
// ジャンルID一覧
const genreIdList = ref<number[]>([])
// プロンプトID一覧
const promptIdList = ref<number[]>([])

// マスタデータからIDのみの配列を抽出
const getGenreIdList = (promptList: MasterData[]) => {
    return promptList.map((genre: MasterData) => genre.id)
}
const getPromptIdList = (promptList: MasterData[]) => {
    const promptIdListQueue: number[] = []
    promptList.map((genre: MasterData) => {
        genre.content.map((prompt: MasterPrompt) => {
            promptIdListQueue.push(prompt.id)
        })
    })

    return promptIdListQueue
}

// マスタデータのindexが1から始まるので新規で配列を作りマスタデータを再挿入
const convertMasterData = (promptList: MasterData[]) => {
    const promptListQueue: MasterData[] = []
    Object.keys(promptList).map((index: string) => {
        const i = parseInt(index)
        promptList[i].id = i
        promptListQueue.push(promptList[i])
    })

    return promptListQueue
}

// nsfwプロパティの値に応じてviewに表示する値を判定
const judgeNsfwDisplay = (limit: string) => {
    switch (limit) {
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

// variationプロパティの値に応じてviewに表示する値を判定
const judgeVariationDisplay = (variation: string | null) => {
    switch (variation) {
        case 'CC':
            return 'マルチカラー'
        case 'CM':
            return 'モノクロ'
        case null:
            return 'なし'
        default:
            return 'なし'
    }
}

// 各プロンプト・ジャンルに表示用の情報を追加
const addDisplayProps = (promptList: MasterData[]) => {
    const promptListQueue: MasterData[] = []

    promptList.map((genre: MasterData) => {
        genre['identifier'] = 'genre'
        genre['nsfw_display'] = judgeNsfwDisplay(genre.nsfw)
        genre['show_prompt'] = false
        promptListQueue.push(genre)

        const promptList: MasterPrompt[] = []
        genre.content.map((prompt: MasterPrompt) => {
            prompt['identifier'] = 'prompt'
            prompt['nsfw_display'] = judgeNsfwDisplay(prompt.nsfw)
            prompt['variation_display'] = judgeVariationDisplay(
                prompt.variation
            )
            promptList.push(prompt)
        })

        genre.content = promptList
    })

    return promptListQueue
}

// DBからマスタデータ一覧を取得、できなかった場合ローカルのjsファイルから取得
const apiManager = new ApiManager()
const getMasterData = async () => {
    const url = apiPath + 'managePrompt.php'
    const response = await apiManager.get(url)
    if (!response.error) return response.content
}

// 選択されたプロンプトデータとジャンルデータ
const selectedPrompt = ref<MasterData | MasterPrompt>(promptInitial)
// マスタデータ一覧の編集ボタン押下時、選択したプロンプトのデータを挿入
const selectPrompt = (
    content: MasterData | MasterPrompt,
    isEdit: boolean = false
) => {
    selectedPrompt.value = content
    selectedPrompt.value.edit = isEdit
}

// 画面表示に必要なデータを取得し挿入
const loadMasterData = async () => {
    promptList.value = addDisplayProps(convertMasterData(await getMasterData()))
    genreIdList.value = getGenreIdList(promptList.value)
    promptIdList.value = getPromptIdList(promptList.value)
}

onMounted(() => loadMasterData())
</script>

<template>
    <HeaderComponent />
    <main class="master-data">
        <section class="top-button-area">
            <div class="generate-json-area">
                <button class="btn-common green">jsonファイル生成</button>
            </div>
            <div class="register-prompt-area">
                <button
                    class="btn-common green"
                    @click="selectPrompt(genreInitial)"
                >
                    ジャンル新規登録
                </button>
                <button
                    class="btn-common blue"
                    @click="selectPrompt(promptInitial)"
                >
                    プロンプト新規登録
                </button>
            </div>
        </section>
        <section class="master-data-list-area">
            <h2>マスタデータ一覧</h2>
            <table
                class="master-data-list"
                v-for="(genre, i) in promptList"
                :key="genre.id"
            >
                <thead>
                    <tr>
                        <th id="id">ID</th>
                        <th id="jp">日本語名</th>
                        <th id="slag">プロンプト名</th>
                        <th id="nsfw">年齢制限</th>
                        <th id="variation">色設定</th>
                        <th id="edit">編集</th>
                    </tr>
                </thead>
                <tbody>
                    <tr
                        class="genre"
                        @click="
                            promptList[i].show_prompt = promptList[i]
                                .show_prompt
                                ? false
                                : true
                        "
                    >
                        <td id="id">{{ genre.id }}</td>
                        <td id="jp">{{ genre.jp }}</td>
                        <td id="slag">{{ genre.slag }}</td>
                        <td id="nsfw">{{ genre.nsfw_display }}</td>
                        <td id="variation">-</td>
                        <td id="edit">
                            <a
                                href="#"
                                @click.prevent.stop="selectPrompt(genre, true)"
                                >編集</a
                            >
                        </td>
                    </tr>
                    <tr
                        class="prompt"
                        v-for="(prompt, j) in genre.content"
                        v-show="promptList[i].show_prompt"
                        :key="prompt.id"
                    >
                        <td id="id">{{ prompt.id }}</td>
                        <td id="jp">{{ prompt.jp }}</td>
                        <td id="tag">{{ prompt.tag }}</td>
                        <td id="nsfw">{{ prompt.nsfw_display }}</td>
                        <td id="variation">{{ prompt.variation_display }}</td>
                        <td id="edit">
                            <a
                                href="#"
                                @click.prevent.stop="selectPrompt(prompt, true)"
                                >編集</a
                            >
                        </td>
                    </tr>
                </tbody>
            </table>
        </section>
        <SelectedPromptComponent
            :selected="selectedPrompt"
            :genreIdList="genreIdList"
            :promptIdList="promptIdList"
            @loadMasterData="loadMasterData"
            @selectPrompt="selectPrompt"
        />
    </main>
</template>
