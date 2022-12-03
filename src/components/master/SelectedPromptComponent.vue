<script setup lang="ts">
import '@/assets/scss/masterData.scss'
// import axios from 'axios'
import { MasterData, MasterPrompt } from '@/assets/ts/Interfaces/Index'
import { ref, watchEffect } from 'vue'

interface Props {
    selected: MasterData | MasterPrompt | undefined
}

const props = defineProps<Props>()

const prompt = ref<MasterData | MasterPrompt | undefined>()
watchEffect(() => {
    prompt.value = props.selected
    console.log(prompt.value)
})
</script>

<template>
    <section class="prompt-manage">
        <h2>マスタデータ編集</h2>
        <dl class="prompt-manage-form" v-if="prompt !== undefined">
            <div>
                <dt>ID</dt>
                <dd><input type="number" v-model="prompt.id" /></dd>
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
            <button class="btn-common blue">送信</button>
        </dl>
        <p v-else>プロンプトまたはジャンルが選択されていません。</p>
    </section>
</template>
