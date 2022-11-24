<template lang="">
    <section class="preset-detail">
        <div v-if="'preset_id' in preset">
            <div class="title-area">
                <h2>{{ preset.description }}</h2>
                <button class="btn-common blue" @click="setRegisterMode(true, 'edit')">編集</button>
            </div>
            <ul class="data-list">
                <li class="image">
                    <img :src="preset.originalImage" alt="">
                </li>
                <li class="prompt copy">
                    <h3>プロンプト</h3>
                    <button 
                    :class="[enhanceBraceMessage === '( )に変換' ? 'btn-common blue':'btn-common green']" 
                    @click="toggleEnhanceBrace()"
                    >{{ enhanceBraceMessage }}</button>
                    <p @click="copyText(preset.commands, 'プロンプト')">{{ preset.commands }}</p>
                </li>
                <li class="prompt-ban copy">
                    <h3>BANプロンプト</h3>
                    <button 
                    :class="[enhanceBraceMessage === '( )に変換' ? 'btn-common blue':'btn-common green']"  
                    @click="toggleEnhanceBrace()"
                    >{{ enhanceBraceMessage }}</button>
                    <p @click="copyText(preset.commands_ban, 'BANプロンプト')">{{ preset.commands_ban }}</p>
                </li>
                <li class="seed copy">
                    <h3>シード値</h3>
                    <p @click="copyText(preset.seed, 'シード値')">{{ preset.seed }}</p>
                </li>
                <li class="resolution">
                    <h3>解像度</h3>
                    <p>{{ preset.resolution }}</p>
                </li>
                <li class="nsfw model separate">
                    <div>
                        <h3>モデル</h3>
                        <p>{{ preset.model }}</p>
                    </div>
                    <div>
                        <h3>nsfw</h3>
                        <p>{{ preset.nsfw_display }}</p>
                    </div>
                </li>
                <li class="sampling separate">
                    <div>
                        <h3>サンプリング回数(Step)</h3>
                        <p>{{ preset.sampling }}</p>
                    </div>
                    <div>
                        <h3>サンプリングアルゴリズム</h3>
                        <p>{{ preset.sampling_algo }}</p>
                    </div>
                </li>
                <li class="scale options separate">
                    <div>
                        <h3>スケール値</h3>
                        <p>{{ preset.scale }}</p>
                    </div>
                    <div>
                        <h3>オプション</h3>
                        <p>{{ preset.options }}</p>
                    </div>
                </li>
                <li class="other">
                    <h3>備考</h3>
                    <p>{{ preset.others }}</p>
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
    emits: ['setAlertText', 'setRegisterMode', ],
    setup(props:any, context:any) {
        const preset = computed(() => props.selected)

        // 強化値の{}と()を切り替える
        const enhanceBraceMessage = ref<string>('( )に変換')
        const toggleEnhanceBrace = () => {
            if (enhanceBraceMessage.value === '( )に変換') {
                enhanceBraceMessage.value = '{ }に変換'
                preset.value.commands = preset.value.commands.replaceAll(/\{/g, '(').replaceAll(/\}/g, ')')
                preset.value.commands_ban = preset.value.commands_ban.replaceAll(/\{/g, '(').replaceAll(/\}/g, ')')
            } else {
                enhanceBraceMessage.value = '( )に変換'
                preset.value.commands = preset.value.commands.replaceAll(/\(/g, '{').replaceAll(/\)/g, '}')
                preset.value.commands_ban = preset.value.commands_ban.replaceAll(/\(/g, '{').replaceAll(/\)/g, '}')
            }
        }

        // クリックした文字列をコピーする
        const alertText = ref<string>('')
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
            alertText.value = preset.value.description + 'の' + name + 'をコピーしました。'
            context.emit('setAlertText', alertText.value)
        }

        // 編集ボタンが押された場合プリセット詳細画面を編集画面に切り替える
        const setRegisterMode = (state: boolean, mode: string) => context.emit('setRegisterMode', state, mode)

        return {
            preset,
            enhanceBraceMessage: enhanceBraceMessage,
            alertText,

            toggleEnhanceBrace,
            copyText,
            setRegisterMode,
        }
    }
}
</script>