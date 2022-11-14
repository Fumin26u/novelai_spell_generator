<template>
    <div>
        <div class="modal-cover" @click="updateModal(false)"></div>
        <div class="modal-window">
            <div>
                <h3>データをDBに登録</h3>
                <small>※<a href="https://nai-pg.com/register/" target="_blank" :style="'font-weight: bold;'">プロンプトセーバー</a>でのログインが必要です。非ログイン時は登録ボタンを押しても登録されません。</small>
            </div>
            <div class="close-modal">
                <span @click="updateModal(false)" class="btn-close"></span>
            </div>
            <dl class="db-form">
                <div>
                    <dt>プロンプト</dt>
                    <dd><input type="text" v-model="promptForDB.commands"></dd>
                </div>
                <div>
                    <dt>BANプロンプト</dt>
                    <dd><input type="text" v-model="promptForDB.commands_ban"></dd>
                </div>
                <div>
                    <dt>説明</dt>
                    <dd><input type="text" v-model="promptForDB.description"></dd>
                </div>
                <div>
                    <dt>シード値</dt>
                    <dd><input type="text" v-model="promptForDB.seed"></dd>
                </div>
                <div>
                    <dt>解像度</dt>
                    <dd>
                        <select v-model="resolution">
                            <option v-for="(resolution, index) in resolutionList" :key="index">{{ resolution }}</option>
                        </select>
                    </dd>
                </div>
                <div>
                    <dt>その他</dt>
                    <dd><textarea v-model="promptForDB.others"></textarea></dd>
                </div>
                <button @click="savePrompt(promptForDB)" class="btn-common add">登録</button>
            </dl>
        </div>
    </div>
</template>
<script lang="ts">
import { ref } from 'vue'
import axios from 'axios'
export default {
    emits: ['updateModal', 'updateText'],
    props: {
        prompts: {
            type: Object,
            required: true,
        },
        copyMessage: String,
        displayModalState: Boolean,
    },
    setup(props: any, context: any) {
        // DB保存モーダルの表示可否
        const isOpenSaveModal = ref<boolean>(props.displayModalState)
        // DB保存用のデータ
        const promptForDB = ref<{[key: string]: string}>(props.prompts)

        const updateText = (text: string) => context.emit('updateText', text)
        const updateModal = (isDisplay: boolean) => context.emit('updateModal', isDisplay)
        
        // プロンプトをDBに保存する
        const savePrompt = (promptForDB: {[key: string]: any}) => {
            if (promptForDB.commands === '') {
                updateText('コマンドが入力されていません。')
                updateModal(false)
                return
            }
            if (typeof promptForDB.seed === 'number') {
                updateText('Seed値が数値で入力されていません。')
                updateModal(false)
                return
            }

            const url = './register/api/registerPreset.php'
            axios.post(url, promptForDB).catch(error => console.log(error))
            updateText('プロンプトをデータベースに登録しました。')
            updateModal(false)
        }

        return {
            promptForDB,
            isOpenSaveModal,
            resolution: 'Portrait (Normal) 512x768',
            resolutionList: [
                'Portrait (Normal) 512x768',
                'LandScape (Normal) 768x512',
                'Square (Normal) 640x640',
                'Portrait (Small) 384x640',
                'LandScape (Small) 640x384',
                'Square (Small) 512x512',
                'Portrait (Large) 512x1024',
                'LandScape (Large) 1024x512',
                'Square (Large) 1024x1024',
            ],
            savePrompt,
            updateModal,
        }
    }
}
</script>
<style lang="scss" scoped>
    .modal-cover {
    width: 100%;
    background: rgba(0,0,0,0.5);
    z-index: 90;
    position: absolute;
    top: 0;
    bottom: 0;
}
.modal-window {
    position: fixed;
    top: 200px;
    left: 50%;
    transform: translateX(-50%);
    max-width: 900px;
    width: 70%;
    height: 540px;
    background: white;
    z-index: 99;
    box-sizing: border-box;
    > div {
        h3 { 
            margin: 8px;
            display: inline-block;
        }
        p {
            display: inline-block;
            margin-left: 8px;
            vertical-align: bottom;
        }
    }
}

.db-form {
    margin: 8px;
    display: block;
    text-align: center;
    > div {
        margin: 8px;
        padding: 8px;
        display: flex;
        justify-content: space-evenly;
        align-items: center;
        font-size: 18px;
        border-bottom: 1px solid #888;
        dt {
            width: 16%;
            text-align: right;
        }
        dd {
            width: 78%;
            text-align: left;
            margin: 0;
        }
    }
    input, select, textarea {
        padding: 4px 8px;
        width: 94%;
        font-size: 16px;
        font-family: 'Yu Gothic Medium';
    }
    textarea {
        height: 80px;
    }
    > button {
        width: 180px;
        padding: 8px;
        font-size: 16px;
        border-radius: 8px;
        font-weight: bold;
        font-family: 'Yu Gothic Medium';
    }
}
</style>