<script setup lang="ts">
import '@/assets/scss/masterData.scss'
import { MasterData, MasterPrompt } from '@/assets/ts/Interfaces/Index'
import apiPath from '@/assets/ts/apiPath'
import ApiManager from '@/components/api/apiManager'
import { ref, watchEffect } from 'vue'

interface Props {
    selected: MasterData | MasterPrompt | undefined
    genreIdList: number[]
    promptIdList: number[]
}
interface Emits {
    (e: 'getMasterData'): Promise<void>
    (
        e: 'selectPrompt',
        content: MasterData | MasterPrompt,
        isEdit: boolean
    ): void
}

const props = defineProps<Props>()
const emit = defineEmits<Emits>()

// 選択されたプロンプト
const prompt = ref<MasterData | MasterPrompt | undefined>()
watchEffect(() => {
    prompt.value = props.selected
})

// genreとpromptのID一覧
const genreIdList = ref<number[]>(props.genreIdList)
const promptIdList = ref<number[]>(props.promptIdList)

// フォーム入力内容のバリデーションを行う
const errorMessage = ref<string[]>([])
const isExistError = () => {
    // エラー内容のリセット
    errorMessage.value = []

    if (prompt.value === undefined) return

    // if (typeof prompt.value.id !== 'number') {
    //     errorMessage.value.push('IDの入力内容が不正です。')
    // }

    if (prompt.value.jp === '') {
        errorMessage.value.push('日本語名が入力されていません。')
    }

    if (prompt.value.identifier === 'genre') {
        if (prompt.value.slag === '') {
            errorMessage.value.push('スラッグが入力されていません。')
        }

        if (!prompt.value.edit && genreIdList.value.includes(prompt.value.id)) {
            errorMessage.value.push('既に使用されているIDです。')
        }
    }

    if (prompt.value.identifier === 'prompt') {
        if (prompt.value.tag === '') {
            errorMessage.value.push('プロンプト名が入力されていません。')
        }

        if (
            prompt.value.genre_id === 0 ||
            genreIdList.value.includes(prompt.value.genre_id)
        ) {
            errorMessage.value.push('存在しないジャンルIDです。')
        }

        if (
            !prompt.value.edit &&
            promptIdList.value.includes(prompt.value.id)
        ) {
            errorMessage.value.push('既に使用されているIDです。')
        }
    }

    return errorMessage.value.length > 0 ? true : false
}

// 入力内容をAPIに送信してデータ登録
const apiManager = new ApiManager()
const registerPrompt = async (method: string = 'save') => {
    // データが存在しない場合エラーを返して強制終了
    if (prompt.value === undefined || prompt.value.id === 0) {
        errorMessage.value.push('送信データが存在しません。')
        return
    }

    const formUrl = apiPath + 'managePrompt.php'
    if (method === 'delete') {
        if (confirm('本当に削除しますか?')) {
            const response = await apiManager.post(formUrl, {
                method: 'delete',
                table: prompt.value.identifier,
                id: prompt.value.id,
            })

            if (response.error) {
                errorMessage.value.push('データベース接続に失敗しました。')
                return
            }

            alert('データを削除しました。')
            emit('getMasterData')
        }
        return
    }

    // 入力内容のバリデーションをし、エラーが無い場合はAPIにデータを送信
    if (isExistError()) return
    const sendData = { ...prompt.value }
    // ジャンル編集の場合プロンプト一覧はデータ量が多いかつ不要なので空にする
    if (sendData.identifier === 'genre') sendData.content = []

    const formData = JSON.stringify(sendData)
    const response = await apiManager.post(formUrl, formData)
    if (response.error) {
        errorMessage.value.push('データベース接続に失敗しました。')
        return
    }

    alert('データ送信が完了しました。')
    emit('getMasterData')
    if (prompt.value !== undefined) {
        emit('selectPrompt', prompt.value, true)
    }
}
</script>

<template>
    <section class="prompt-manage" v-if="prompt !== undefined">
        <div class="title-area">
            <h2>マスタデータ編集</h2>
            <div class="button-area">
                <button class="btn-common blue" @click="registerPrompt()">
                    送信
                </button>
                <button
                    class="btn-common red"
                    v-if="prompt.id !== 0"
                    @click="registerPrompt('delete')"
                >
                    削除
                </button>
            </div>
        </div>
        <p
            class="response-message"
            v-for="(message, index) in errorMessage"
            :key="index"
        >
            {{ message }}
        </p>
        <dl class="prompt-manage-form">
            <div>
                <dt>ID</dt>
                <dd>
                    <input
                        type="number"
                        v-model="prompt.id"
                        :readonly="prompt.edit"
                    />
                </dd>
            </div>
            <div>
                <dt>日本語名</dt>
                <dd><input type="text" v-model="prompt.jp" /></dd>
            </div>
            <div>
                <dt>年齢制限</dt>
                <dd>
                    <input
                        type="radio"
                        v-model="prompt.nsfw"
                        value="A"
                        id="nsfw_a"
                    />
                    <label for="nsfw_a">全年齢</label>
                    <input
                        type="radio"
                        v-model="prompt.nsfw"
                        value="C"
                        id="nsfw_c"
                    />
                    <label for="nsfw_c">R-15</label>
                    <input
                        type="radio"
                        v-model="prompt.nsfw"
                        value="Z"
                        id="nsfw_z"
                    />
                    <label for="nsfw_z">R-18</label>
                </dd>
            </div>
            <section v-if="prompt.identifier === 'prompt'">
                <div>
                    <dt>プロンプト名</dt>
                    <dd><input type="text" v-model="prompt.tag" /></dd>
                </div>
                <div>
                    <dt>ジャンルID</dt>
                    <dd><input type="text" v-model="prompt.genre_id" /></dd>
                </div>
                <div>
                    <dt>カラーバリエーション</dt>
                    <dd>
                        <input
                            type="radio"
                            v-model="prompt.variation"
                            value="CC"
                            id="variation_cc"
                        />
                        <label for="variation_cc">マルチカラー</label>
                        <input
                            type="radio"
                            v-model="prompt.variation"
                            value="CM"
                            id="variation_cm"
                        />
                        <label for="variation_cm">モノクロ</label>
                        <input
                            type="radio"
                            v-model="prompt.variation"
                            value="null"
                            id="variation_none"
                        />
                        <label for="variation_none">なし</label>
                    </dd>
                </div>
                <div>
                    <dt>詳細</dt>
                    <dd><input type="text" v-model="prompt.detail" /></dd>
                </div>
            </section>
            <section v-if="prompt.identifier === 'genre'">
                <div>
                    <dt>スラッグ</dt>
                    <dd><input type="text" v-model="prompt.slag" /></dd>
                </div>
                <div>
                    <dt>キャプション</dt>
                    <dd><input type="text" v-model="prompt.caption" /></dd>
                </div>
            </section>
            <button class="btn-common blue" @click="registerPrompt()">
                送信
            </button>
        </dl>
    </section>
</template>
