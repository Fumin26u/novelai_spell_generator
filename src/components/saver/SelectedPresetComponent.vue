<template lang="">
    <section class="preset-detail">
        <div v-if="selectedPreset !== null">
            <div class="title-area">
                <h2>{{ selectedPreset.description }}</h2>
                <a :href="'./register/commands.php?preset_id=' + selectedPreset.preset_id" class="btn-common blue">編集</a>
                <!-- <button class="btn-common blue">編集</button>
                <button class="btn-common green" :style="'display:none;'">保存</button> -->
            </div>
            <ul class="data-list">
                <li class="image">
                    <img :src="selectedPreset.originalImage" alt="">
                </li>
                <li class="nsfw">
                    <h3>nsfw</h3>
                    <p>{{ selectedPreset.nsfw_display }}</p>
                </li>
                <li class="prompt copy">
                    <h3>プロンプト</h3>
                    <button 
                        :class="[enhanceBraceMessage === '( )に変換' ? 'btn-common blue':'btn-common green']" 
                        @click="toggleEnhanceBrace()"
                    >{{ enhanceBraceMessage }}</button>
                    <p @click="copyText(selectedPreset.commands, 'プロンプト')">{{ selectedPreset.commands }}</p>
                </li>
                <li class="prompt-ban copy">
                    <h3>BANプロンプト</h3>
                    <button 
                        :class="[enhanceBraceMessage === '( )に変換' ? 'btn-common blue':'btn-common green']"  
                        @click="toggleEnhanceBrace()"
                    >{{ enhanceBraceMessage }}</button>
                    <p @click="copyText(selectedPreset.commands_ban, 'BANプロンプト')">{{ selectedPreset.commands_ban }}</p>
                </li>
                <li class="seed copy">
                    <h3>シード値</h3>
                    <p @click="copyText(selectedPreset.seed, 'シード値')">{{ selectedPreset.seed }}</p>
                </li>
                <li class="resolution">
                    <h3>解像度</h3>
                    <p>{{ selectedPreset.resolution }}</p>
                </li>
                <li class="other">
                    <h3>備考</h3>
                    <p>{{ selectedPreset.others }}</p>
                </li>
            </ul>
        </div>
    </section>
</template>
<script lang="ts">
import { ref, computed } from 'vue'
import '../../assets/scss/savedPrompt.scss'

export default {
    props: {
        selected: {
            type: Object,
        }
    },
    emits: ['setCopyAlertText', ],
    setup(props:any, context:any) {
        const selectedPreset = computed(() => props.selected)

        // 強化値の{}と()を切り替える
        const enhanceBraceMessage = ref<string>('( )に変換')
        const toggleEnhanceBrace = () => {
            if (enhanceBraceMessage.value === '( )に変換') {
                enhanceBraceMessage.value = '{ }に変換'
                selectedPreset.value.commands = selectedPreset.value.commands.replaceAll(/\{/g, '(').replaceAll(/\}/g, ')')
                selectedPreset.value.commands_ban = selectedPreset.value.commands_ban.replaceAll(/\{/g, '(').replaceAll(/\}/g, ')')
            } else {
                enhanceBraceMessage.value = '( )に変換'
                selectedPreset.value.commands = selectedPreset.value.commands.replaceAll(/\(/g, '{').replaceAll(/\)/g, '}')
                selectedPreset.value.commands_ban = selectedPreset.value.commands_ban.replaceAll(/\(/g, '{').replaceAll(/\)/g, '}')
            }
        }

        // クリックした文字列をコピーする
        const copyAlertText = ref<string>('')
        const copyText = (text: string, name: string) => {
            const href = location.href.substring(0,5)
            if (href === 'https') {
                navigator.clipboard.writeText(text)
            } else if (href === 'http:') {
                const input = document.createElement('input')
                document.body.appendChild(input)
                input.value = text
                input.select()
                document.execCommand('copy')
                document.body.removeChild(input)
            }
            copyAlertText.value = selectedPreset.value.description + 'の' + name + 'をコピーしました。'
            context.emit('setCopyAlertText', copyAlertText.value)
        }

        return {
            selectedPreset,
            enhanceBraceMessage: enhanceBraceMessage,
            copyAlertText,

            toggleEnhanceBrace,
            copyText,
        }
    }
}
</script>