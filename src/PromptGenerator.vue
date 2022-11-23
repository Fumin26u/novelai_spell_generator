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
                            v-for="(genre, i) in tagsList"                 
                            :key="genre.slag"
                            :style="[genre.display ? 'display:block' : 'display:none']"
                        >
                            <div class="description">
                                <div>
                                    <p class="genre">{{ genre.jp }}</p>
                                    <p class="caption">{{ genre.caption }}</p>
                                </div>
                                <div>
                                    <span @click="tagsList[i]['show_all'] = true" v-if="!tagsList[i]['show_all']">▼</span>
                                    <span @click="tagsList[i]['show_all'] = false" v-if="tagsList[i]['show_all']">▲</span>
                                </div>
                            </div>
                            <div :style="[tagsList[i]['show_all'] ? 'max-height:none;' : 'max-height:240px;']">
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
                <div class="prompt-settings">
                    <div class="description">
                        <h2>設定プロンプト一覧</h2>
                        <small>選択中: {{hoverPromptName}}</small>
                        <div class="manual-input-area">
                            <label :for="'manual-input'">手動追加</label>
                            <input type="text" :id="'manual-input'" v-model="manualInput">
                            <button class="btn-common green" @click="addManualPromptToList(manualInput)">追加</button>
                        </div>
                    </div>
                    <draggable 
                        class="prompt" 
                        v-model="setSpells"
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
                                    <button @click="setSpells[index].enhance -= 1" class="btn-common red">－</button>
                                    <span>{{ element.enhance }}</span>
                                    <button @click="setSpells[index].enhance += 1" class="btn-common green">＋</button>
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
                                <button @click="convertToNovelAITags(setSpells)" class="btn-common green">呪文生成</button>
                                <button @click="toggleEnhanceBrace()" :class="[enhanceBraceMessage === '( )に変換' ? 'btn-common blue':'btn-common green']">{{ enhanceBraceMessage }}</button>
                            </div>
                            <div class="save">
                                <button @click="copyToClipboard(spellsNovelAI)" class="btn-common orange">コピー</button>
                                <button 
                                    @click="openSaveModal(setSpells, true)" class="btn-common blue open-save-modal"
                                >保存</button>
                            </div>                
                        </div>
                        <span class="copy-alert">{{ copyAlert }}</span>           
                    </div>
                </div>
            </div>
        </div>
        <ModalDBComponent
            :prompts="spellsNovelAI"
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
import draggable from 'vuedraggable'
import './assets/scss/promptGenerator.scss'
import HeaderComponent from './components/HeaderComponent.vue'
import ModalDBComponent from './components/ModalDBComponent.vue'

export default {
    components: {
        draggable,
        HeaderComponent,
        ModalDBComponent,
    },
    setup() {
        // 表示するタグ一覧
        const tagsList = ref<{[key: string]: any}[]>([])
        // nsfwコンテンツの表示可否
        const displayNsfw = ref<string>('A')
        // Hover中のタグ
        const hoverPromptName = ref<string>('')
        // アップロード用プロンプト
        const spellsByUserText = ref<string>('')
        // 手動入力内容
        const manualInputText = ref<string>('')
        // セットされているタグ(プロンプト)のキュー
        const setSpells = ref<{[key: string]: any}[]>([])
        // カラー設定可能なプロンプトの選択されたカラーを格納する配列
        const selectedColor = ref<{[key: string]: string}>({})

        // 指定されたタグ名に該当するプロンプトを選択状態にする
        const selectPromptFromSearch = (word: string): void => {
            tagsList.value.map((genre: {[key: string]: any}, i: number) => {
                genre.content.map((prompt: {[key: string]: any}, j: number) => {           
                    if (prompt.tag === word)  tagsList.value[i].content[j].selected = true              
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
            tagsList.value.map ((genre: {[key: string]: any}, i: number) => {
                tagsList.value[i]['display'] = judgeIsDisplay(limit, tagsList.value[i].nsfw)
                genre.content.map((_: {[key: string]: any}, j: number) => {
                    tagsList.value[i].content[j]['display'] = judgeIsDisplay(limit, tagsList.value[i].content[j].nsfw)
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
                    tagsList.value = convertJsonToTagList(response.data)
                    setDisplayNsfw(displayNsfw.value)
                })
                .catch(error => {
                    tagsList.value = convertJsonToTagList(master_data)
                    setDisplayNsfw(displayNsfw.value)
                    console.log(error)
                })
        }
        
        // 年齢制限切り替えボタンを押した際の処理
        const toggleDisplayNsfw = (limit: string): void => {
            setSpells.value.map(prompt => selectPromptFromSearch(prompt.tag))
            displayNsfw.value = limit
            setDisplayNsfw(displayNsfw.value)
        }

        // 手動入力でのプロンプトの追加
        const addManualPromptToList = (input: string, enhanceCount: number = 0): void => {
            let isAlreadySetPrompt = false
            // 既に追加されているプロンプト名の場合追加しない
            setSpells.value.map((prompt: {[key: string]: any}) => {
                if (input.trim() === prompt.tag) isAlreadySetPrompt = true
            })
            if (!isAlreadySetPrompt && input.trim() !== '') {
                setSpells.value.push({
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
            
            const setPrompt: {[key: string]: any} = {}
            for (let i = 0; i < tagsList.value.length; i++) {
                for (let j = 0; j < tagsList.value[i].content.length; j++) {
                    const prompt = tagsList.value[i].content[j]
                    if (prompt.tag === tagname || (colorTagJP.value.trim() !== '' && promptAfterSpace === prompt.tag)) {

                        if (tagsList.value[i].content[j].variation !== null && colorTagJP.value !== '') {
                            setPrompt['tag'] = promptAfterSpace
                            setPrompt['jp'] = tagsList.value[i].content[j].jp + ' (' + colorTagJP.value + ')'
                        } else {
                            setPrompt['tag'] = tagname
                            setPrompt['jp'] = tagsList.value[i].content[j].jp
                        }
                        
                        setPrompt['output_prompt'] = tagname
                        setPrompt['parentTag'] = tagsList.value[i].jp
                        setPrompt['detail'] = ''
                        setPrompt['slag'] = tagname.replace(' ', '_')
                        setPrompt['enhance'] = enhanceCount
                        setPrompt['index'] = i + ',' + j
                        setPrompt['nsfw'] = tagsList.value[i].content[j].nsfw
                        setPrompt['error'] = ''
                        switch (tagsList.value[i].content[j].variation) {
                            case 'CC':
                                setPrompt['color_list'] = colorMulti
                                break
                            case 'CM':
                                setPrompt['color_list'] = colorMono
                                break
                            default:
                                setPrompt['color_list'] = null
                                break
                        }

                        tagsList.value[i].content[j].selected = true
                        // 該当のプロンプトがnsfwワードだった場合R-18モードにする
                        if (displayNsfw.value === 'A' || (displayNsfw.value === 'C' && tagsList.value[i].content[j].nsfw === 'Z')) {
                            displayNsfw.value = tagsList.value[i].content[j].nsfw
                        } 
                        setDisplayNsfw(displayNsfw.value)
                        setSpells.value.push(setPrompt)
                        return
                    } 
                }
            }
            addManualPromptToList(tagname, enhanceCount)
            return
        }

        // 既存のタグがアップロードされた場合、セットキューに対象値を追加
        const uploadSpell = (spell: string): void => {
            if (spell.trim() === '') return
            // 既存の設定プロンプトリストと手動入力欄をリセット
            setSpells.value = []
            manualInputText.value = ''
            tagsList.value.map((genre: {[key: string]: any}, i: number) => {
                genre.content.map((_: any, j: number) => {
                    tagsList.value[i].content[j].selected = false
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

        // タグのセットキューに挿入
        const toggleSetPromptList = (i: number, j: number): void => { 
            const queue = tagsList.value[i].content[j]
            
            queue['output_prompt'] = queue.tag
            queue['enhance'] = 0
            switch (tagsList.value[i].content[j].variation) {
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
            const selected = tagsList.value[i].content[j].selected

            if (!selected) {
                if (!setSpells.value.includes(queue)) {
                    setSpells.value.push(queue)
                    tagsList.value[i].content[j].selected = true
                }
            } else {
                for (let index = 0; index < setSpells.value.length; index++) {
                    if (setSpells.value[index].tag === queue.tag) {
                        setSpells.value.splice(index, 1)
                        tagsList.value[i].content[j].selected = false
                    }
                }
            }
        }

        // カラーバリエーションのあるプロンプトで色付きが選択された場合プロンプト名を変換
        const changePromptColor = (colorTag: {[key: string]: string}, index: number): void => {
            const braceIndex = setSpells.value[index].jp.indexOf('(')
            if (braceIndex !== -1) {
                setSpells.value[index].jp = setSpells.value[index].jp.substring(0, braceIndex-1)
            }
            if (colorTag.prompt !== 'none')  {
                setSpells.value[index].jp = setSpells.value[index].jp + ' (' + colorTag.jp + ')'
                setSpells.value[index].output_prompt = colorTag.prompt + ' ' + setSpells.value[index].tag
            }
            selectedColor.value = {}
        } 

        // セットキューから指定したプロンプトを削除
        const deleteSetPromptList = (index: number): void => {
            if (setSpells.value[index].index !== null) {
                const tagsIndexList = setSpells.value[index].index.split(',')
                const i = parseInt(tagsIndexList[0])
                const j = parseInt(tagsIndexList[1])
                
                tagsList.value[i].content[j].selected = false
            }
            setSpells.value.splice(index, 1)
        }

        // 生成されたNovelAI形式のプロンプト
        const spellsNovelAI = ref('')
        // キューにセットされているタグをNovelAIで使える形に変換する
        const convertToNovelAITags = (spells: {[key: string]: any}[]): void => {
            const text = ref('')
            
            spells.map(spell => {
                // タグの付与
                // 強化値が0の場合そのまま追加
                if (spell.enhance === 0) {
                    text.value += spell.output_prompt
                } else if (spell.enhance > 0) {
                    // 強化値が1以上の場合前後に{}を数値分追加
                    text.value += '{'.repeat(spell.enhance) + spell.output_prompt + '}'.repeat(spell.enhance) 
                } else if (spell.enhance < 0) {
                    // 強化値が-1以下の場合前後に[]を数値分追加
                    const num = spell.enhance * -1
                    text.value += '['.repeat(num) + spell.output_prompt + ']'.repeat(num)
                }
                text.value += ', '
            })         
            spellsNovelAI.value = text.value
        }

        // 強化値の()と{}を切り替える
        const enhanceBraceMessage = ref<string>('( )に変換')
        const toggleEnhanceBrace = () => {
            if (enhanceBraceMessage.value === '( )に変換') {
                enhanceBraceMessage.value = '{ }に変換'
                spellsNovelAI.value = spellsNovelAI.value.replaceAll(/\{/g, '(').replaceAll(/\}/g, ')')
            } else {
                enhanceBraceMessage.value = '( )に変換'
                spellsNovelAI.value = spellsNovelAI.value.replaceAll(/\(/g, '{').replaceAll(/\)/g, '}')
            }
        }

        // DB保存モーダルの表示可否
        const isOpenSaveModal = ref(false)
        // DB保存用のモーダルを開く
        const openSaveModal = (promptList: {[key: string]: any}[], modalState: boolean): void => {
            convertToNovelAITags(promptList)
            isOpenSaveModal.value = modalState
        }
        
        // プロンプトをコピーした際のアラート
        const copyAlert = ref('')
        // プロンプトをクリップボードにコピーする
        const copyToClipboard = (text: string): void => {
            navigator.clipboard.writeText(text)
            copyAlert.value = 'クリップボードにコピーしました。'
        }

        // コンポーネントから受け取ったアラートテキストを更新する
        const updateAlertText = (text: string): string => copyAlert.value = text
        // モーダルの表示状態を更新する
        const updateModalState = (isDisplay: boolean): boolean => isOpenSaveModal.value = isDisplay

        // 生成したプロンプトが編集状態か判定
        const isEditNAIPrompt = ref(false)

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
            tagsList,
            user_id,
            hoverPromptName,
            setSpells,
            spellsNovelAI,
            copyAlert,
            isOpenSaveModal,
            displayNsfw: displayNsfw,
            manualInput: manualInputText,
            spellsByUser: spellsByUserText,
            selectedColor: selectedColor,
            enhanceBraceMessage,
            isEditNAIPrompt: isEditNAIPrompt,
            uploadSpell,
            toggleSetPromptList,
            toggleDisplayNsfw,
            changePromptColor,
            addManualPromptToList,
            deleteSetPromptList,
            convertToNovelAITags,
            toggleEnhanceBrace,
            openSaveModal,
            copyToClipboard,
            updateAlertText,
            updateModalState,
        }
    }
}
</script>