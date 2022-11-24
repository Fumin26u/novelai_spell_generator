<template>
    <div class="prompt-settings">
        <div class="description">
            <h2>設定プロンプト一覧</h2>
            <small>選択中: {{hoverPrompt}}</small>
            <div class="manual-input-area">
                <label :for="'manual-input'">手動追加</label>
                <input type="text" :id="'manual-input'" v-model="manualInput">
                <button class="btn-common green" @click="addManualPrompt(manualInput)">追加</button>
            </div>
        </div>
        <draggable 
            class="prompt" 
            v-model="setPrompt"
            item-key="index"
            handle=".prompt-variation-select"
        >
            <template #item="{element, index}">
                <div class="draggable">
                    <div class="prompt-variation-select">
                        <div class="prompt-name">
                            <div>
                                <span class="caption">{{ element.parentTag }}</span>
                                <p :class="['nsfw_' + element.nsfw]">{{ element.jp }}</p>
                            </div>
                        </div>
                        <div v-if="element.color_list !== null">
                            <span class="caption">色の設定</span>
                            <select 
                                :class="['nsfw_' + element.nsfw]"
                                v-model="selectedColor" 
                                @change="changePromptColor(selectedColor, index)"
                            >
                                <option disabled :value="{}">(選択)</option>
                                <option v-for="color in element.color_list" :key="color.prompt" :value="color">{{ color.jp }}</option>
                            </select>
                        </div>
                    </div>
                    <div class="enhance-area">
                        <button @click="setPrompt[index].enhance -= 1" class="btn-common red">－</button>
                        <span>{{ element.enhance }}</span>
                        <button @click="setPrompt[index].enhance += 1" class="btn-common green">＋</button>
                    </div>
                    <div class="delete-area">
                        <button @click="deleteSetPromptList(index)" class="btn-common red">削除</button>
                    </div>
                </div>
            </template>
        </draggable>
        <div class="output-area">
            <div class="text-area">
                <p class="output"><b>出力値</b> (クリックで編集可)<br>
                    <span v-if="!isEditNAIPrompt" @click="isEditNAIPrompt = true">
                        {{ spellsNovelAI }}
                    </span>
                </p>
                <textarea v-if="isEditNAIPrompt" v-model="spellsNovelAI" @keyup.enter="isEditNAIPrompt = false"></textarea>
            </div>
            <div class="button-area">
                <div class="generate">
                    <button @click="convertToNovelAITags(setPrompt)" class="btn-common green">呪文生成</button>
                    <button @click="toggleEnhanceBrace()" :class="[enhanceBraceMessage === '( )に変換' ? 'btn-common blue':'btn-common green']">{{ enhanceBraceMessage }}</button>
                </div>
                <div class="save">
                    <button @click="copyToClipboard(spellsNovelAI)" class="btn-common orange">コピー</button>
                    <button 
                        @click="openSaveModal(setPrompt, true)" class="btn-common blue open-save-modal"
                    >保存</button>
                </div>                
            </div>
            <span class="copy-alert">{{ copyAlert }}</span>           
        </div>
    </div>
</template>
<script lang="ts">
import { colorMulti, colorMono } from './assets/ts/colorVariation'
import { ref, onMounted } from 'vue'
import draggable from 'vuedraggable'
import './assets/scss/promptGenerator.scss'

export default {
    props: {
        setSpells: {
            type: Object,
        },
        hoverPromptName: {
            type:String
        }
    },
    emits: ['addManualPrompt', ],
    setup(props: any, context: any) {
        const setPrompt = ref<{[key: string]: any}[]>(props.setSpells)
        const hoverPrompt = ref<string>(props.hoverPromptName)

        // 手動入力でのプロンプトの追加
        const manualInput = ref<string>('')
        const addManualPrompt = (input: string, enhanceCount: number = 0): void => {
            context.emit('addManualPrompt', input, enhanceCount)
        }

        // カラーバリエーションのあるプロンプトで色付きが選択された場合プロンプト名を変換
        const selectedColor = ref<{[key: string]: string}>({})
        const changePromptColor = (colorTag: {[key: string]: string}, index: number): void => {
            const braceIndex = setPrompt.value[index].jp.indexOf('(')
            if (braceIndex !== -1) {
                setPrompt.value[index].jp = setPrompt.value[index].jp.substring(0, braceIndex-1)
            }
            if (colorTag.prompt !== 'none')  {
                setPrompt.value[index].jp = setPrompt.value[index].jp + ' (' + colorTag.jp + ')'
                setPrompt.value[index].output_prompt = colorTag.prompt + ' ' + setPrompt.value[index].tag
            }
            selectedColor.value = {}
        }

        return {
            setPrompt,
            hoverPrompt,
            manualInput: manualInput,
            selectedColor: selectedColor,

            addManualPrompt,
            changePromptColor,
        }
    } 
}
</script>