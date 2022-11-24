<template>
    <div>
        <div class="modal-cover" @click="updateModal(false)"></div>
        <div class="modal-window">
            <div>
                <h3>データをDBに登録</h3>
                <small>※<a href="https://nai-pg.com/register/login.php" target="_blank" :style="'font-weight: bold;'">プロンプトセーバー</a>でのログインが必要です。</small>
                <div class="senior-mode-setting">
                    <input type="checkbox" v-model="isSeniorMode" id="senior-mode">
                    <label for="senior-mode">上級者向け設定あり</label>
                </div>
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
                    <dd><input type="text" v-model="preset.commands"></dd>
                </div>
                <div>
                    <dt>BANプロンプト</dt>
                    <dd><input type="text" v-model="preset.commands_ban"></dd>
                </div>
                <div>
                    <dt>説明</dt>
                    <dd><input type="text" v-model="preset.description"></dd>
                </div>
                <div>
                    <dt>年齢制限</dt>
                    <dd>
                        <input type="radio" v-model="preset.nsfw" value="A" id="nsfw_a">
                        <label for="nsfw_a">全年齢</label>
                        <input type="radio" v-model="preset.nsfw" value="C" id="nsfw_c">
                        <label for="nsfw_c">R-15</label>
                        <input type="radio" v-model="preset.nsfw" value="Z" id="nsfw_z">
                        <label for="nsfw_z">R-18</label>
                    </dd>
                </div>
                <div>
                    <dt>シード値</dt>
                    <dd><input type="text" v-model="preset.seed"></dd>
                </div>
                <div>
                    <dt>解像度</dt>
                    <dd>
                        <select v-model="preset.resolution">
                            <option v-for="(resolution, index) in resolutionList" :key="index">{{ resolution }}</option>
                        </select>
                    </dd>
                </div>
                <section v-if="isSeniorMode" class="senior-settings">
                    <div>
                        <dt>モデル名</dt>
                        <dd>
                            <input type="radio" v-model="preset.model" value="NovelAI" id="model_NovelAI">
                            <label for="model_NovelAI">NovelAI</label>
                            <input type="radio" v-model="preset.model" value="Waifu Diffusion" id="model_Waifu_Diffusion">
                            <label for="model_Waifu_Diffusion">Waifu Diffusion</label>
                            <input type="radio" v-model="preset.model" value="Anything V3" id="model_Anything_V3">
                            <label for="model_Anything_V3">Anything V3</label>
                        </dd>
                    </div>
                    <div>
                        <dt>サンプリング回数<br>(Step)</dt>
                        <dd><input type="number" step="1" v-model="preset.sampling"></dd>
                    </div>
                    <div>
                        <dt>サンプリング<br>アルゴリズム</dt>
                        <dd>
                            <select v-model="preset.sampling_algo">
                                <option v-for="(algorithm, index) in algorithms" :key="index">{{ algorithm }}</option>
                            </select>
                        </dd>
                    </div>
                    <div>
                        <dt>Scale値</dt>
                        <dd><input type="number" step="1" v-model="preset.scale"></dd>
                    </div>
                    <div>
                        <dt>オプション</dt>
                        <dd>
                            <div>
                                <input type="checkbox" v-model="preset.options" value="Restore Faces" id="Restore_Faces">
                                <label for="Restore_Faces">Restore Faces(顔修復)</label>
                            </div>
                            <div>
                                <input type="checkbox" v-model="preset.options" value="Tiling" id="Tiling">
                                <label for="Tiling">Tiling(テクスチャ生成)</label>
                            </div>
                            <div>
                                <input type="checkbox" v-model="preset.options" value="Highres. Fix" id="Highres_Fix">
                                <label for="Highres_Fix">Highres. Fix(高解像度修正)</label>
                            </div>
                        </dd>
                    </div>
                </section>
                <div>
                    <dt>その他</dt>
                    <dd><textarea v-model="preset.others"></textarea></dd>
                </div>
                <button @click="savePreset()" class="btn-common green">登録</button>
            </dl>
        </div>
    </div>
</template>
<script lang="ts">
import registerPath from '@/assets/ts/registerPath'
import { resolutionList, algorithms } from '@/assets/ts/dbSelectList'
import { ref, watchEffect } from 'vue'
import axios from 'axios'
import '../../assets/scss/modalDB.scss'

export default {
    emits: ['updateModal',],
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
        const preset = ref<{[key: string]: any}>({
            image: '',
            from: 'generator',
            commands: '',
            commands_ban: '',
            description: '',
            nsfw: 'A',
            seed: '',
            resolution: 'Portrait (Normal) 512x768',
            model: 'NovelAI',
            sampling: 28,
            sampling_algo: 'Euler a',
            scale: 11,
            options: ['Highres. Fix'],
            others: '',
        })
        watchEffect(() => preset.value.commands = props.prompts)

        const updateModal = (isDisplay: boolean) => context.emit('updateModal', isDisplay)
        
        // 上級者向け設定の表示可否
        const isSeniorMode = ref<boolean>(false)
        
        // プリセットをDBに保存する
        const savePreset = () => {
            if (preset.value.commands === '') {
                alert('コマンドが入力されていません。')
                updateModal(false)
                return
            }
            if (preset.value.seed !== '' && isNaN(parseInt(preset.value.seed))) {
                alert('Seed値が数値で入力されていません。')
                updateModal(false)
                return
            }

            const formUrl = registerPath + 'api/registerPreset.php'
            const sendData = {...preset.value}
            // 上級者向け設定をOFFにしている場合、該当項目のデータはNULLにする
            if (!isSeniorMode.value) {
                sendData.model = null
                sendData.sampling = null
                sendData.sampling_algo = null
                sendData.scale = null
                sendData.options = null
            } else {
                sendData.options = sendData.options.join(',')
            }
            const formData = JSON.stringify(sendData)
            
            axios.post(formUrl, formData).then(() => {
                alert('プロンプトをデータベースに登録しました。')
            }).catch(error => {
                alert('データベース接続に失敗しました。')
                console.log(error)
            })

            updateModal(false)
        }

        // 画像データを取得
        const uploadImage = (event: Event) => {
            if (event.target instanceof HTMLInputElement && event.target.files) {
                const file = event.target.files[0]
                const reader = new FileReader()
                
                reader.onloadend = () => {
                    preset.value.image = reader.result
                }
                reader.readAsDataURL(file)
            }
        }

        return {
            preset,
            isOpenSaveModal,
            resolutionList,
            algorithms,
            isSeniorMode: isSeniorMode,
            savePreset,
            updateModal,
            uploadImage,
        }
    }
}
</script>