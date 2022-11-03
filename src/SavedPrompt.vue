<template>
    <div class="main">
        <HeaderComponent></HeaderComponent> 
    </div>
</template>
<script>
import HeaderComponent from './components/HeaderComponent.vue'
import './assets/style.scss'
import { onMounted } from 'vue'
import axios from 'axios'

export default {
    components: {
        HeaderComponent,
    },
    setup() {
        // 画面ロード時、APIからログインユーザーの登録プロンプト一覧を取得
        onMounted(async() => {
            const url = '/register/api/getPreset.php'
            await axios.get(url)
                .then(response => console.log(response))
                .catch(error => console.log(error))
        })
    }
}
</script>
<style lang="scss" scoped>
    
</style>