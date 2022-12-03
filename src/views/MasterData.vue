<script setup lang="ts">
import HeaderComponent from '@/components/HeaderComponent.vue'
import { MasterData, MasterPrompt } from '@/assets/ts/Interfaces/Index'
import axios from 'axios'
import registerPath from '@/assets/ts/registerPath'
import { ref, onMounted } from 'vue'

//  DBから取得したマスタデータ一覧
const promptList = ref<MasterData[]>([])

// マスタデータのindexが1から始まるので新規で配列を作りマスタデータを再挿入
const convertMasterData = (masterData: MasterData[]) => {
    const promptListQueue: MasterData[] = []
    Object.keys(masterData).map((index: string) => {
        promptListQueue.push(masterData[parseInt(index)])
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
        genre['nsfw_display'] = judgeNsfwDisplay(genre.nsfw)
        promptListQueue.push(genre)
        
        const promptList: MasterPrompt[] = []
        genre.content.map((prompt: MasterPrompt) => {
            prompt['nsfw_display'] = judgeNsfwDisplay(prompt.nsfw)
            prompt['variation_display'] = judgeVariationDisplay(prompt.variation)
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

// ログインユーザーIDを取得
const user_id = ref<string>('')
const getUserInfo = (userId: string) => (user_id.value = userId)

onMounted(() => getMasterData())
</script>

<template>
    <HeaderComponent @getUserInfo="getUserInfo" />
    <main class="master-data"></main>
</template>
