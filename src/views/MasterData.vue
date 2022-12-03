<script setup lang="ts">
import HeaderComponent from '@/components/HeaderComponent.vue'
import '@/assets/scss/masterData.scss'
import axios from 'axios'
import { MasterData, MasterPrompt } from '@/assets/ts/Interfaces/Index'
import registerPath from '@/assets/ts/registerPath'
import { ref, onMounted } from 'vue'

//  DBから取得したマスタデータ一覧
const promptList = ref<MasterData[]>([])

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
const getMasterData = async (): Promise<void> => {
    const url = registerPath + 'api/getMasterData.php?from=spell_generator'
    await axios
        .get(url)
        .then((response) => {
            promptList.value = addDisplayProps(convertMasterData(response.data))
        })
        .catch((error) => {
            console.log(error)
        })
}

// 選択されたプロンプトデータとジャンルデータ
const selectedPrompt = ref<MasterData | MasterPrompt>()
// マスタデータ一覧の編集ボタン押下時、選択したプロンプトのデータを挿入
const selectPrompt = (content: MasterData | MasterPrompt) => {
    selectedPrompt.value = content
    console.log(selectedPrompt.value)
}

// ログインユーザーIDを取得
const user_id = ref<string>('')
const getUserInfo = (userId: string) => (user_id.value = userId)

onMounted(() => getMasterData())
</script>

<template>
    <HeaderComponent @getUserInfo="getUserInfo" />
    <main class="master-data">
        <section class="master-data-list-area">
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
                                @click.prevent.stop="selectPrompt(genre)"
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
                                @click.prevent.stop="selectPrompt(prompt)"
                                >編集</a
                            >
                        </td>
                    </tr>
                </tbody>
            </table>
        </section>
    </main>
</template>
