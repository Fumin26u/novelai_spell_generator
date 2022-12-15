<script setup lang="ts">
import { SetPrompt, ColorVariation } from '@/assets/ts/Interfaces/Index'
import { ref, computed, watchEffect } from 'vue'
import draggable from 'vuedraggable'
import user_id from '@/components/api/getUserId'
import '@/assets/scss/promptGenerator.scss'

interface Props {
    setPromptList: SetPrompt[]
    hoverPromptName?: string
}

interface Emits {
    (e: 'updateSetPrompt', childSetPrompt: SetPrompt[]): SetPrompt[]
    (e: 'addManualPrompt', uploadPromptName: string, enhanceCount: number): void
    (e: 'unSelectedPrompt', promptListIndex?: string): void
    (e: 'openSaveModal', modalState: boolean, output: string): void
}

const props = defineProps<Props>()

const emit = defineEmits<Emits>()

const setPrompt = ref<SetPrompt[]>([])
watchEffect(() => (setPrompt.value = props.setPromptList))
// カーソルを合わせているプロンプトの英語名
const hoverPrompt = computed(() => props.hoverPromptName)

// 親子間のプロンプト設定を同期させる
watchEffect(() => emit('updateSetPrompt', setPrompt.value))

// 手動入力でのプロンプトの追加
const manualInput = ref<string>('')
const addManualPrompt = (input: string, enhanceCount = 0): void => {
    emit('addManualPrompt', input, enhanceCount)
}

// プロンプト設定欄からボタンが押されたプロンプトを削除
const deleteSetPrompt = (index: number) => {
    // 親コンポーネントにプロンプトのインデックスを伝えて選択を解除
    emit('unSelectedPrompt', setPrompt.value[index].index)

    setPrompt.value.splice(index, 1)
}

// カラーバリエーションのあるプロンプトで色付きが選択された場合プロンプト名を変換
const selectedColor = ref<ColorVariation>({ prompt: '', jp: '' })
const changePromptColor = (colorTag: ColorVariation, index: number): void => {
    // 一度表示名とタグ名をリセット
    const braceIndex = setPrompt.value[index].jp.indexOf('(')
    setPrompt.value[index].output_prompt = setPrompt.value[index].tag

    if (braceIndex !== -1) {
        setPrompt.value[index].jp = setPrompt.value[index].jp.substring(
            0,
            braceIndex - 1
        )
    }
    if (colorTag.prompt !== 'none') {
        setPrompt.value[index].jp =
            setPrompt.value[index].jp + ' (' + colorTag.jp + ')'
        setPrompt.value[index].output_prompt =
            colorTag.prompt + ' ' + setPrompt.value[index].tag
    }
    selectedColor.value = { prompt: '', jp: '' }
}

// 生成されたNovelAI形式のプロンプト
const outputPrompt = ref('')
const enhanceBraceText = ref<string>('( )に変換')
// キューにセットされているタグをNovelAIで使える形に変換する
const convertToOutputPrompt = (setPrompt: SetPrompt[]): void => {
    const text = ref('')

    setPrompt.map((prompt) => {
        // タグの付与
        // 強化値が0の場合そのまま追加
        if (prompt.enhance === 0) {
            text.value += prompt.output_prompt
        } else if (prompt.enhance > 0) {
            // 強化値が1以上の場合前後に{}または()を数値分追加
            if (enhanceBraceText.value === '( )に変換') {
                text.value +=
                    '{'.repeat(prompt.enhance) +
                    prompt.output_prompt +
                    '}'.repeat(prompt.enhance)
            } else if (enhanceBraceText.value === '{ }に変換') {
                text.value +=
                    '('.repeat(prompt.enhance) +
                    prompt.output_prompt +
                    ')'.repeat(prompt.enhance)
            }
        } else if (prompt.enhance < 0) {
            // 強化値が-1以下の場合前後に[]を数値分追加
            const num = prompt.enhance * -1
            text.value +=
                '['.repeat(num) + prompt.output_prompt + ']'.repeat(num)
        }
        text.value += ', '
    })

    outputPrompt.value = text.value
}

// 強化値の()と{}を切り替える
const toggleEnhanceBrace = () => {
    if (enhanceBraceText.value === '( )に変換') {
        enhanceBraceText.value = '{ }に変換'
        outputPrompt.value = outputPrompt.value
            .replaceAll(/\{/g, '(')
            .replaceAll(/\}/g, ')')
    } else {
        enhanceBraceText.value = '( )に変換'
        outputPrompt.value = outputPrompt.value
            .replaceAll(/\(/g, '{')
            .replaceAll(/\)/g, '}')
    }
}

// DB保存用のモーダルを開く
const openSaveModal = (promptList: SetPrompt[], modalState: boolean): void => {
    convertToOutputPrompt(promptList)
    emit('openSaveModal', modalState, outputPrompt.value)
}

// プロンプトをコピーした際のアラート
const copyAlertText = ref('')
// プロンプトをクリップボードにコピーする
const copyToClipboard = (text: string): void => {
    navigator.clipboard.writeText(text)
    copyAlertText.value = 'クリップボードにコピーしました。'
}

// 出力値の編集状態
const isEditPrompt = ref<boolean>(false)
</script>

<template>
    <div class="prompt-settings">
        <div class="description">
            <div class="title-area">
                <h2>設定プロンプト一覧</h2>
                <small>選択中: {{ hoverPrompt }}</small>
            </div>
            <div class="manual-input-area">
                <label :for="'manual-input'">手動追加</label>
                <input type="text" :id="'manual-input'" v-model="manualInput" />
                <button
                    class="btn-common green"
                    @click="addManualPrompt(manualInput)"
                >
                    追加
                </button>
            </div>
        </div>
        <draggable
            class="prompt"
            v-model="setPrompt"
            item-key="index"
            handle=".prompt-variation-select"
        >
            <template #item="{ element, index }">
                <div class="draggable">
                    <div class="prompt-variation-select">
                        <div class="prompt-name">
                            <div>
                                <span class="caption">{{
                                    element.parentTag
                                }}</span>
                                <p :class="['nsfw_' + element.nsfw]">
                                    {{ element.jp }}
                                </p>
                            </div>
                        </div>
                        <div v-if="element.color_list !== null">
                            <span class="caption">色の設定</span>
                            <select
                                :class="['nsfw_' + element.nsfw]"
                                v-model="selectedColor"
                                @change="
                                    changePromptColor(selectedColor, index)
                                "
                            >
                                <option
                                    disabled
                                    :value="{ prompt: '', jp: '' }"
                                >
                                    (選択)
                                </option>
                                <option
                                    v-for="color in element.color_list"
                                    :key="color.prompt"
                                    :value="color"
                                >
                                    {{ color.jp }}
                                </option>
                            </select>
                        </div>
                    </div>
                    <div class="enhance-area">
                        <button
                            @click="setPrompt[index].enhance -= 1"
                            class="btn-common red"
                        >
                            －
                        </button>
                        <span>{{ element.enhance }}</span>
                        <button
                            @click="setPrompt[index].enhance += 1"
                            class="btn-common green"
                        >
                            ＋
                        </button>
                    </div>
                    <div class="delete-area">
                        <button
                            @click="deleteSetPrompt(index)"
                            class="btn-common red"
                        >
                            削除
                        </button>
                    </div>
                </div>
            </template>
        </draggable>
        <div class="output-area">
            <div class="text-area">
                <p class="output">
                    <b>出力値</b> (クリックで編集可)<br />
                    <span v-show="!isEditPrompt" @click="isEditPrompt = true">
                        {{ outputPrompt }}
                    </span>
                </p>
                <textarea
                    v-show="isEditPrompt"
                    v-model="outputPrompt"
                    @keyup.enter="isEditPrompt = false"
                ></textarea>
            </div>
            <div class="button-area">
                <div class="generate">
                    <button
                        @click="convertToOutputPrompt(setPrompt)"
                        class="btn-common green"
                    >
                        呪文生成
                    </button>
                    <button
                        @click="toggleEnhanceBrace()"
                        :class="[
                            enhanceBraceText === '( )に変換'
                                ? 'btn-common blue'
                                : 'btn-common green',
                        ]"
                    >
                        {{ enhanceBraceText }}
                    </button>
                </div>
                <div class="save">
                    <button
                        @click="copyToClipboard(outputPrompt)"
                        class="btn-common orange"
                    >
                        コピー
                    </button>
                    <button
                        v-if="user_id !== ''"
                        @click="openSaveModal(setPrompt, true)"
                        class="btn-common blue open-save-modal"
                    >
                        保存
                    </button>
                </div>
            </div>
            <span class="copy-alert">{{ copyAlertText }}</span>
        </div>
    </div>
</template>
