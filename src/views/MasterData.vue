<script setup lang="ts">
import axios from 'axios'
import registerPath from '@/assets/ts/registerPath';
import { ref, onMounted } from 'vue'

// DBからマスタデータ一覧を取得、できなかった場合ローカルのjsファイルから取得
const masterData = ref<any>([])
const getMasterData = async (): Promise<void> => {
    const url = registerPath + 'api/getMasterData.php?from=spell_generator'
    await axios
        .get(url)
        .then((response) => {
            console.log(response.data)
            masterData.value = response.data
        })
        .catch((error) => {
            console.log(error)
        })
}

onMounted(() => getMasterData())
</script>

<template>
    <main class="master-data">

    </main>
</template>
