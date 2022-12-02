<script setup lang="ts">
import '@/assets/scss/savedPrompt.scss'
import { SearchData } from '@/assets/ts/Interfaces/Index'
import { ref } from 'vue'

interface Props {
    searchBoxData: SearchData
}
interface Emits {
    (e: 'getPresetData', postData: SearchData): Promise<void>
}

const props = defineProps<Props>()
const emit = defineEmits<Emits>()

// 検索ボックスの入力内容
const searchData = ref<SearchData>(props.searchBoxData)

const execSearch = () => {
    emit('getPresetData', searchData.value)
}
</script>

<template lang="">
    <div class="search-box">
        <dl>
            <div>
                <dt>年齢制限</dt>
                <dd>
                    <input
                        type="checkbox"
                        v-model="searchData.age"
                        value="A"
                        id="noLimit"
                    />
                    <label for="noLimit">全年齢</label>
                    <input
                        type="checkbox"
                        v-model="searchData.age"
                        value="C"
                        id="r15"
                    />
                    <label for="r15">R-15</label>
                    <input
                        type="checkbox"
                        v-model="searchData.age"
                        value="Z"
                        id="nsfw"
                    />
                    <label for="nsfw">R-18</label>
                </dd>
            </div>
            <div>
                <dt>ワード検索</dt>
                <dd>
                    <div>
                        <label>検索項目:</label>
                        <input
                            type="checkbox"
                            v-model="searchData.item"
                            value="description"
                            id="description"
                        />
                        <label for="description">タイトル</label>
                        <input
                            type="checkbox"
                            v-model="searchData.item"
                            value="commands"
                            id="prompt"
                        />
                        <label for="prompt">プロンプト</label>
                        <input
                            type="checkbox"
                            v-model="searchData.item"
                            value="commands_ban"
                            id="prompt_ban"
                        />
                        <label for="prompt_ban">BANプロンプト</label>
                        <input
                            type="checkbox"
                            v-model="searchData.item"
                            value="seed"
                            id="seed"
                        />
                        <label for="seed">シード値</label>
                        <input
                            type="checkbox"
                            v-model="searchData.item"
                            value="resolution"
                            id="resolution"
                        />
                        <label for="resolution">解像度</label>
                        <input
                            type="checkbox"
                            v-model="searchData.item"
                            value="others"
                            id="others"
                        />
                        <label for="others">備考</label>
                    </div>
                    <div>
                        <label for="search-word">検索ワード:</label>
                        <input
                            type="text"
                            v-model="searchData.word"
                            id="search-word"
                        />
                    </div>
                </dd>
            </div>
            <div>
                <dt>ソート</dt>
                <dd>
                    <div>
                        <input
                            type="radio"
                            v-model="searchData.sort"
                            value="description"
                            id="sort-description"
                        />
                        <label for="sort-description">説明</label>
                        <input
                            type="radio"
                            v-model="searchData.sort"
                            value="seed"
                            id="sort-seed"
                        />
                        <label for="sort-seed">シード値</label>
                        <input
                            type="radio"
                            v-model="searchData.sort"
                            value="resolution"
                            id="sort-resolution"
                        />
                        <label for="sort-resolution">解像度</label>
                        <input
                            type="radio"
                            v-model="searchData.sort"
                            value="created_at"
                            id="sort-created"
                        />
                        <label for="sort-created">作成日付</label>
                        <input
                            type="radio"
                            v-model="searchData.sort"
                            value="updated_at"
                            id="sort-updated"
                        />
                        <label for="sort-updated">更新日付</label>
                    </div>
                    <div>
                        <input
                            type="radio"
                            v-model="searchData.order"
                            value="asc"
                            id="sort-asc"
                        />
                        <label for="sort-asc">昇順</label>
                        <input
                            type="radio"
                            v-model="searchData.order"
                            value="desc"
                            id="sort-desc"
                        />
                        <label for="sort-desc">降順</label>
                    </div>
                </dd>
            </div>
            <button @click="execSearch()" class="btn-common green">検索</button>
        </dl>
    </div>
</template>
