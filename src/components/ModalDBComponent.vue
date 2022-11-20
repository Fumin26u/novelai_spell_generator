<template>
    <div>
        <div class="modal-cover" @click="updateModal(false)"></div>
        <div class="modal-window">
            <div>
                <h3>データをDBに登録</h3>
                <small>※<a href="https://nai-pg.com/register/login.php" target="_blank" :style="'font-weight: bold;'">プロンプトセーバー</a>でのログインが必要です。</small>
            </div>
            <div class="close-modal">
                <span @click="updateModal(false)" class="btn-close"></span>
            </div>
            <dl class="db-form">
                <div>
                    <dt>画像</dt>
                    <dd><input type="file" @change="uploadImage" accept="image/*"></dd>
                </div>
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
                    <dt>年齢制限</dt>
                    <dd>
                        <input type="radio" v-model="promptForDB.nsfw" value="A" id="nsfw_a">
                        <label for="nsfw_a">全年齢</label>
                        <input type="radio" v-model="promptForDB.nsfw" value="C" id="nsfw_c">
                        <label for="nsfw_c">R-15</label>
                        <input type="radio" v-model="promptForDB.nsfw" value="Z" id="nsfw_z">
                        <label for="nsfw_z">R-18</label>
                    </dd>
                </div>
                <div>
                    <dt>シード値</dt>
                    <dd><input type="text" v-model="promptForDB.seed"></dd>
                </div>
                <div>
                    <dt>解像度</dt>
                    <dd>
                        <select v-model="promptForDB.resolution">
                            <option v-for="(resolution, index) in resolutionList" :key="index">{{ resolution }}</option>
                        </select>
                    </dd>
                </div>
                <div>
                    <dt>その他</dt>
                    <dd><textarea v-model="promptForDB.others"></textarea></dd>
                </div>
                <button @click="savePrompt()" class="btn-common green">登録</button>
            </dl>
        </div>
    </div>
</template>
<script lang="ts">
import { ref, watchEffect } from 'vue'
import axios from 'axios'
import '../assets/scss/modalDB.scss'

export default {
    emits: ['updateModal', 'updateText'],
    props: {
        prompts: {
            type: String,
            required: true,
        },
        copyMessage: String,
        displayModalState: Boolean,
    },
    setup(props: any, context: any) {
        // DB保存モーダルの表示可否
        const isOpenSaveModal = ref<boolean>(props.displayModalState)
        // DB保存用のデータ
        const promptForDB = ref<{[key: string]: any}>({
            from: 'generator',
            commands: '',
            commands_ban: '',
            description: '',
            nsfw: 'A',
            seed: '',
            resolution: 'Portrait (Normal) 512x768',
            others: '',
        })
        watchEffect(() => promptForDB.value.commands = props.prompts)
        // DB保存用の画像
        const postImage = ref<any>({})

        const updateText = (text: string) => context.emit('updateText', text)
        const updateModal = (isDisplay: boolean) => context.emit('updateModal', isDisplay)
        
        // プロンプトをDBに保存する
        const savePrompt = () => {
            if (promptForDB.value.commands === '') {
                updateText('コマンドが入力されていません。')
                updateModal(false)
                return
            }
            if (promptForDB.value.seed !== '' && isNaN(parseInt(promptForDB.value.seed))) {
                updateText('Seed値が数値で入力されていません。')
                updateModal(false)
                return
            }

            const formUrl = './register/api/registerPreset.php'
            const formData = new FormData()
            const formConfig = {
                headers: {
                    'content-type': 'multipart/form-data',
                    'X-HTTP-Method-Override': 'PUT',
                }
            }
            
            formData.append('text_content', JSON.stringify(promptForDB.value))
            formData.append('image', postImage.value)
            console.log(formData.get('image'))

            axios.post(formUrl, formData, formConfig).then((response) => {
                console.log(response)
                updateText('プロンプトをデータベースに登録しました。')
            }).catch(error => {
                updateText('データベース接続に失敗しました。')
                console.log(error)
            })
            updateModal(false)
        }

        // 画像データを取得
        const uploadImage = (event: Event) => {
            if (event.target instanceof HTMLInputElement && event.target.files) {
                postImage.value = event.target.files[0]
                console.log(postImage.value)
            }
        }

        return {
            promptForDB,
            isOpenSaveModal,
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
            uploadImage,
        }
    }
}
</script>