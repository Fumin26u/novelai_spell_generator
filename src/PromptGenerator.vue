<template>
    <div class="top-content">
        <div class="main">
            <HeaderComponent :user="user_id"></HeaderComponent>
            <div class="content">
                <div class="main-content">
                    <section class="user-setting-area">
                        <div class="upload-prompt">
                            <label :id="'upload-prompt'">プロンプトをアップロード</label>
                            <input type="text" :id="'upload-prompt'" v-model="spellsByUser" @keyup.enter="uploadSpell(spellsByUser)">
                            <button @click="uploadSpell(spellsByUser)" class="btn-common green">アップロード</button>
                        </div>
                        <div class="toggle-nsfw">
                            <button @click="toggleDisplayNsfw('C')" v-if="displayNsfw === 'A'" class="btn-common blue">全年齢</button>
                            <button @click="toggleDisplayNsfw('Z')" v-if="displayNsfw === 'C'" class="btn-common green">R-15</button>
                            <button @click="toggleDisplayNsfw('A')" v-if="displayNsfw === 'Z'" class="btn-common pink">R-18</button>            
                        </div>
                    </section>
                    <section class="tag-list">
                        <div 
                            class="spell-list"
                            v-for="(genre, i) in promptList"                 
                            :key="genre.slag"
                            :style="[genre.display ? 'display:block' : 'display:none']"
                        >
                            <div class="description">
                                <div>
                                    <p class="genre">{{ genre.jp }}</p>
                                    <p class="caption">{{ genre.caption }}</p>
                                </div>
                                <div>
                                    <span @click="promptList[i]['show_all'] = true" v-if="!promptList[i]['show_all']">▼</span>
                                    <span @click="promptList[i]['show_all'] = false" v-if="promptList[i]['show_all']">▲</span>
                                </div>
                            </div>
                            <div :style="[promptList[i]['show_all'] ? 'max-height:none;' : 'max-height:240px;']">
                                <div 
                                    v-for="(prompt, j) in genre.content" 
                                    :key="prompt.slag" 
                                    :style="[prompt.display ? 'display:block; position:relative;' : 'display:none']">
                                    <button 
                                        :class="[
                                            prompt.selected ? 'btn-toggle selected' : 'btn-toggle', 
                                            'nsfw_' + prompt.nsfw
                                        ]" 
                                        @click="toggleSetPromptList(i, j)"
                                        @mouseover="hoverPromptName = prompt.tag"
                                        @mouseleave="hoverPromptName = ''"
                                    >
                                    {{ prompt.jp }}
                                    </button>
                                </div>
                            </div>
                        </div>
                    </section>
                </div>
                <SetPromptComponent 
                    :setPromptList="setPrompt"
                    :hoverPromptName="hoverPromptName"
                    @updateSetPrompt="updateSetPrompt"
                    @addManualPrompt="addManualPrompt"
                    @deleteSetPrompt="deleteSetPrompt"
                    @openSaveModal="openSaveModal"
                />
            </div>
        </div>
        <ModalDBComponent
            :prompts="outputPrompt"
            :copyMessage="copyAlert"
            :displayModalState="isOpenSaveModal"
            @updateModal="updateModalState"
            @updateText="updateAlertText"
            :style="[isOpenSaveModal ? 'display: block' : 'display: none']"
        />
    </div>
    <router-view></router-view>
</template>

<script lang="ts">
import master_data from '@/assets/ts/master_data'
import registerPath from '@/assets/ts/registerPath'
import { colorMulti, colorMono } from './assets/ts/colorVariation'
import { ref, onMounted } from 'vue'
import axios from 'axios'
import './assets/scss/promptGenerator.scss'
import HeaderComponent from './components/HeaderComponent.vue'
import SetPromptComponent from './components/generator/SetPromptComponent.vue'
import ModalDBComponent from './components/generator/ModalDBComponent.vue'

export default {
    components: {
        HeaderComponent,
        SetPromptComponent,
        ModalDBComponent,
    },
    setup() {
        // 表示するタグ一覧
        const promptList = ref<{[key: string]: any}[]>([])
        // nsfwコンテンツの表示可否
        const displayNsfw = ref<string>('A')
        // Hover中のタグ
        const hoverPromptName = ref<string>('')
        // アップロード用プロンプト
        const spellsByUserText = ref<string>('')
        // 手動入力内容
        const manualInputText = ref<string>('')
        // セットされているタグ(プロンプト)のキュー
        const setPrompt = ref<{[key: string]: any}[]>([])
        // カラー設定可能なプロンプトの選択されたカラーを格納する配列
        const selectedColor = ref<{[key: string]: string}>({})

        // 指定されたタグ名に該当するプロンプトを選択状態にする
        const selectPromptFromSearch = (word: string): void => {
            promptList.value.map((genre: {[key: string]: any}, i: number) => {
                genre.content.map((prompt: {[key: string]: any}, j: number) => {           
                    if (prompt.tag === word)  promptList.value[i].content[j].selected = true              
                })
            })
        }

        // nsfwコンテンツの表示設定
        const judgeIsDisplay = (limit: string, promptNsfw: string): boolean => {
            switch (limit) {
                case 'A':
                    return promptNsfw === 'A' ? true:false
                case 'C':
                    return promptNsfw === 'A' || promptNsfw === 'C' ? true:false          
                case 'Z':
                    return true
                default:
                    return promptNsfw === 'A' ? true:false
            }
        }
        
        const setDisplayNsfw = (limit: string): void => {
            promptList.value.map ((genre: {[key: string]: any}, i: number) => {
                promptList.value[i]['display'] = judgeIsDisplay(limit, promptList.value[i].nsfw)
                genre.content.map((_: {[key: string]: any}, j: number) => {
                    promptList.value[i].content[j]['display'] = judgeIsDisplay(limit, promptList.value[i].content[j].nsfw)
                })
            })
        } 

        // JSON文字列にしたマスタデータをJSオブジェクトの配列に変換
        const convertJsonToTagList = (json: string | object): {[key: string]: any}[] => {
            const jsonObj = typeof json === 'string' ? JSON.parse(json) : json
            const commandListQueue: {[key: string]: any}[] = []
            Object.keys(jsonObj).map(index => commandListQueue.push(jsonObj[index]))

            // 配列に表示に必要なデータを挿入
            const commandList: {[key: string]: any}[] = []
            commandListQueue.map((genre: {[key: string]: any}, i: number) => {
                genre['show_all'] = false
                genre.caption = genre.caption === "" ? "-" : genre.caption

                genre.content.map((prompt: {[key: string]: any}, j: number) => {  
                    prompt['slag'] = prompt.tag.replace(' ', '_')
                    prompt['selected'] = false
                    prompt['parentTag'] = genre.jp
                    prompt['index'] = i + ',' + j                    
                })
                commandList.push(genre)
            })

            return commandList
        }

        // 画面読み込み時にマスタデータ一覧を取得、できなかった場合ローカルのjsファイルから取得
        const getMasterData = async(): Promise<void> => {
            const url = registerPath + 'api/getMasterData.php?from=spell_generator'
            await axios.get(url)
                .then(response => {
                    promptList.value = convertJsonToTagList(response.data)
                    setDisplayNsfw(displayNsfw.value)
                })
                .catch(error => {
                    promptList.value = convertJsonToTagList(master_data)
                    setDisplayNsfw(displayNsfw.value)
                    console.log(error)
                })
        }
        
        // 年齢制限切り替えボタンを押した際の処理
        const toggleDisplayNsfw = (limit: string): void => {
            setPrompt.value.map(prompt => selectPromptFromSearch(prompt.tag))
            displayNsfw.value = limit
            setDisplayNsfw(displayNsfw.value)
        }

        // 手動入力でのプロンプトの追加
        const addManualPrompt = (input: string, enhanceCount: number = 0): void => {
            let isAlreadySetPrompt = false
            // 既に追加されているプロンプト名の場合追加しない
            setPrompt.value.map((prompt: {[key: string]: any}) => {
                if (input.trim() === prompt.tag) isAlreadySetPrompt = true
            })
            if (!isAlreadySetPrompt && input.trim() !== '') {
                setPrompt.value.push({
                    tag: input,
                    jp: input,
                    output_prompt: input,
                    parentTag: '手動',
                    detail: '',
                    slag: input.replace(' ', '_'),
                    enhance: enhanceCount,
                    color_list: null,
                    index: null,
                    nsfw: 'A'
                })
            }
        }

        // タグのセットキューに挿入
        const toggleSetPromptList = (i: number, j: number): void => { 
            const queue = promptList.value[i].content[j]
            
            queue['output_prompt'] = queue.tag
            queue['enhance'] = 0
            switch (promptList.value[i].content[j].variation) {
                case 'CC':
                    queue['color_list'] = colorMulti
                    break
                case 'CM':
                    queue['color_list'] = colorMono
                    break
                default:
                    queue['color_list'] = null
                    break
            }
            const selected = promptList.value[i].content[j].selected

            if (!selected) {
                if (!setPrompt.value.includes(queue)) {
                    setPrompt.value.push(queue)
                    promptList.value[i].content[j].selected = true
                }
            } else {
                for (let index = 0; index < setPrompt.value.length; index++) {
                    if (setPrompt.value[index].tag === queue.tag) {
                        setPrompt.value.splice(index, 1)
                        promptList.value[i].content[j].selected = false
                    }
                }
            }
        }
        
        // タグ一覧から指定のタグ名を検索し、親タグと日本語名を返す
        const setPromptFromUploadText = (tagname: string, enhanceCount: number): void => {            
            // カラーリング付プロンプト用の定数。AfterSpaceがプロンプト名本体、BeforSpaceがカラーバリュー。
            const promptAfterSpace: string = tagname.substring(tagname.indexOf(' ')+1)
            const promptBeforeSpace: any = tagname.substring(0, tagname.indexOf(' '))
            const colorTagJP = ref<string>('')
            // カラーバリュー設定が存在する場合プロンプトの日本語名を変更
            if (promptBeforeSpace !== -1) {
                colorMulti.map(color => {
                    if (promptBeforeSpace === color.prompt) {
                        colorTagJP.value = color.jp
                    }
                })
            }
            
            const promptQueue: {[key: string]: any} = {}
            for (let i = 0; i < promptList.value.length; i++) {
                for (let j = 0; j < promptList.value[i].content.length; j++) {
                    const prompt = promptList.value[i].content[j]
                    if (prompt.tag === tagname || (colorTagJP.value.trim() !== '' && promptAfterSpace === prompt.tag)) {

                        if (promptList.value[i].content[j].variation !== null && colorTagJP.value !== '') {
                            promptQueue['tag'] = promptAfterSpace
                            promptQueue['jp'] = promptList.value[i].content[j].jp + ' (' + colorTagJP.value + ')'
                        } else {
                            promptQueue['tag'] = tagname
                            promptQueue['jp'] = promptList.value[i].content[j].jp
                        }
                        
                        promptQueue['output_prompt'] = tagname
                        promptQueue['parentTag'] = promptList.value[i].jp
                        promptQueue['detail'] = ''
                        promptQueue['slag'] = tagname.replace(' ', '_')
                        promptQueue['enhance'] = enhanceCount
                        promptQueue['index'] = i + ',' + j
                        promptQueue['nsfw'] = promptList.value[i].content[j].nsfw
                        promptQueue['error'] = ''
                        switch (promptList.value[i].content[j].variation) {
                            case 'CC':
                                promptQueue['color_list'] = colorMulti
                                break
                            case 'CM':
                                promptQueue['color_list'] = colorMono
                                break
                            default:
                                promptQueue['color_list'] = null
                                break
                        }

                        promptList.value[i].content[j].selected = true
                        // 該当のプロンプトがnsfwワードだった場合R-18モードにする
                        if (displayNsfw.value === 'A' || (displayNsfw.value === 'C' && promptList.value[i].content[j].nsfw === 'Z')) {
                            displayNsfw.value = promptList.value[i].content[j].nsfw
                        } 
                        setDisplayNsfw(displayNsfw.value)
                        setPrompt.value.push(promptQueue)
                        return
                    } 
                }
            }
            addManualPrompt(tagname, enhanceCount)
            return
        }

        // 既存のタグがアップロードされた場合、セットキューに対象値を追加
        const uploadSpell = (spell: string): void => {
            if (spell.trim() === '') return
            // 既存の設定プロンプトリストと手動入力欄をリセット
            setPrompt.value = []
            manualInputText.value = ''
            promptList.value.map((genre: {[key: string]: any}, i: number) => {
                genre.content.map((_: any, j: number) => {
                    promptList.value[i].content[j].selected = false
                })
            })

            // タグごと配列の要素にする
            const tagsQueue = spell.split(',')
            const tags = tagsQueue.map(tag => tag.trim())
            tags.map((tag: string, index: number) => {
                if(tag.trim() === " " || tag.trim() === "") {
                    tags.splice(index, 1)
                } else {
                    // 文字の前後に{}または[]がある場合、その数分強化値を追加する
                    const enhanceCount = ref(0)
                    if (tag.match(/\{/g) !== null) {
                        enhanceCount.value = tag.match(/\{/g)!.length
                    } else if (tag.match(/\[/g) !== null) {
                        enhanceCount.value = tag.match(/\[/g)!.length * -1 
                    } else if (tag.match(/\(/g) !== null) {
                        enhanceCount.value = tag.match(/\(/g)!.length
                    } 
                    const tagname = tag.replace(/\{/g, "").replace(/\}/g, "").replace(/\[/g, "").replace(/\]/g, "").replace(/\(/g, "").replace(/\)/g, "")

                    // 設定プロンプトリストに必要情報を挿入 
                    setPromptFromUploadText(tagname, enhanceCount.value)      
                }
            })
        }

        // 子コンポーネントから伝えられたプロンプト設定欄の内容を更新
        const updateSetPrompt = (childSetPrompt: {[key: string]: any}[]) => {
            setPrompt.value = childSetPrompt
        }

        // セットキューから指定したプロンプトを削除
        const deleteSetPrompt = (promptListIndex: string): void => {
            const tagsIndexList = promptListIndex.split(',')
            const i = parseInt(tagsIndexList[0])
            const j = parseInt(tagsIndexList[1])
            
            promptList.value[i].content[j].selected = false
        }

        // DB保存モーダルの表示可否
        const isOpenSaveModal = ref<boolean>(false)
        const outputPrompt = ref<string>('')
        // DB保存用のモーダルを開く
        const openSaveModal = (modalState: boolean, output: string): void => {
            outputPrompt.value = output
            isOpenSaveModal.value = modalState
        }
        
        // プロンプトをコピーした際のアラート
        const copyAlert = ref('')

        // コンポーネントから受け取ったアラートテキストを更新する
        const updateAlertText = (text: string): string => copyAlert.value = text
        // モーダルの表示状態を更新する
        const updateModalState = (isDisplay: boolean): boolean => isOpenSaveModal.value = isDisplay

        // ログインユーザーIDを取得
        const user_id = ref<string>('')
        const getUserInfo = async() => {
            const url = registerPath + 'api/getUserInfo.php'
            axios.get(url)
                .then(response => user_id.value = response.data.user_id)
                .catch(error => console.log(error))
        }

        // 画面読み込み時、ログインユーザーIDを取得し、DBからマスタデータを取得。できない場合はローカルから取得。
        onMounted(() => {
            getUserInfo()
            getMasterData()
        })
        
        return {
            promptList,
            user_id,
            hoverPromptName,
            setPrompt,
            copyAlert,
            isOpenSaveModal,
            displayNsfw: displayNsfw,
            manualInput: manualInputText,
            spellsByUser: spellsByUserText,
            selectedColor: selectedColor,
            outputPrompt: outputPrompt,
            uploadSpell,
            toggleSetPromptList,
            toggleDisplayNsfw,
            addManualPrompt,
            updateSetPrompt,
            deleteSetPrompt,
            openSaveModal,
            updateAlertText,
            updateModalState,
        }
    }
}
</script>