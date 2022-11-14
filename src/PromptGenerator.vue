<template>
    <div class="top-content">
        <div class="main">
            <HeaderComponent></HeaderComponent>
            <div class="content">
                <div class="main-content">
                    <section class="upload-prompt">
                        <div class="upload">
                            <label :id="'upload-prompt'">プロンプトをアップロード</label>
                            <input type="text" :id="'upload-prompt'" v-model="spellsByUser">
                            <button @click="uploadSpell(spellsByUser)" class="btn-common add">アップロード</button>
                        </div>
                        <div class="toggle-nsfw">
                            <button @click="displayNsfw = displayNsfw ? false:true, displayNsfwContent()" :class="[displayNsfw ? 'btn-common pink': 'btn-common blue']">
                                {{ displayNsfw ? 'R-18' : '全年齢' }}
                            </button>
                        </div>
                    </section>
                    <section class="tag-list">
                        <div 
                            class="spell-list"
                            v-for="(genre, i) in tagsList"                 
                            :key="genre.slag"
                            :style="[!genre.nsfw || displayNsfw ? 'display:block' : 'display:none']"
                        >
                            <div class="description">
                                <div>
                                    <p class="genre">{{ genre.jp }}</p>
                                    <p class="caption">{{ genre.caption }}</p>
                                </div>
                                <div>
                                    <span @click="tagsList[i]['display'] = true" v-if="!tagsList[i]['display']">▼</span>
                                    <span @click="tagsList[i]['display'] = false" v-if="tagsList[i]['display']">▲</span>
                                </div>
                            </div>
                            <div :style="[tagsList[i]['display'] ? 'max-height:none;' : 'max-height:240px;']">
                                <div 
                                    v-for="(prompt, j) in genre.content" 
                                    :key="prompt.slag" 
                                    :style="[prompt.display ? 'display:block; position:relative;' : 'display:none']">
                                    <button 
                                        :class="[prompt.selected ? 'btn-toggle selected' : 'btn-toggle', prompt.nsfw ? ' nsfw' : '']" 
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
                <div class="spell-settings">
                    <div class="description">
                        <h2>設定プロンプト一覧</h2>
                        <small>選択中: {{hoverPromptName}}</small>
                        <div class="manual-input-area">
                            <label :for="'manual-input'">手動追加</label>
                            <input type="text" :id="'manual-input'" v-model="manualInput">
                            <button class="btn-common add" @click="addManualPromptToList(manualInput)">追加</button>
                        </div>
                    </div>
                    <draggable 
                        class="spells" 
                        v-model="setSpells"
                        item-key="index"
                    >
                        <template #item="{element, index}">
                            <div>
                                <div class="prompt-variation-select">
                                    <div class="prompt-name">
                                        <div>
                                            <span class="caption">{{ element.parentTag }}</span>
                                            <p :style="[element.nsfw ? 'color:tomato;' : 'color:blue;']">{{ element.jp }}</p>
                                        </div>
                                    </div>
                                    <div v-if="element.variation !== null">
                                        <span class="caption">色の設定</span>
                                        <select 
                                            v-if="element.variation === 'CC'"
                                            :style="[element.nsfw ? 'color:tomato;' : 'color:blue;']" 
                                            v-model="selectedColor" 
                                            @change="changePromptColor(selectedColor, index)"
                                        >
                                            <option disabled :value="{}">(選択)</option>
                                            <option v-for="color in colorMultiColor" :key="color.prompt" :value="color">{{ color.jp }}</option>
                                        </select>
                                        <select 
                                            v-if="element.variation === 'CM'"
                                            :style="[element.nsfw ? 'color:tomato;' : 'color:blue;']" 
                                            v-model="selectedColor" 
                                            @change="changePromptColor(selectedColor, index)"
                                        >
                                            <option disabled :value="{}">(選択)</option>
                                            <option v-for="color in colorMonochrome" :key="color.prompt" :value="color">{{ color.jp }}</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="enhance-area">
                                    <button @click="enhanceSpell(index, -1)" class="btn-common delete">－</button>
                                    <span>{{ element.enhance }}</span>
                                    <button @click="enhanceSpell(index, 1)" class="btn-common add">＋</button>
                                </div>
                                <div class="delete-area">
                                    <button @click="deleteSetPromptList(index)" class="btn-common delete">削除</button>
                                </div>
                            </div>
                        </template>
                    </draggable>
                    <div class="output-area">
                        <div class="text-area">
                            <p class="output"><b>出力値</b> (クリックで編集可)<br>
                                <span v-if="!isEditNAIPrompt" @click="toggleIsEditNAIPrompt(true)">
                                    {{ spellsNovelAI }}
                                </span>
                            </p>
                            <textarea v-if="isEditNAIPrompt" v-model="spellsNovelAI" @keyup.enter="toggleIsEditNAIPrompt(false)"></textarea>
                        </div>
                        <div class="button-area">
                            <div class="generate">
                                <button @click="convertToNovelAITags(setSpells)" class="btn-common add">呪文生成</button>
                                <button @click="toggleTagsFor()" class="btn-common blue" :style="'padding: 6px;'">{{ selectedPromptFor }}</button>
                            </div>
                            <div class="save">
                                <button @click="copyToClipboard(spellsNovelAI)" class="btn-common copy">コピー</button>
                                <button @click="openSaveModal(setSpells), isOpenSaveModal = true" class="btn-common blue">保存</button>
                            </div>                          
                        </div>
                        <span class="copy-alert">{{ copyAlert }}</span>           
                    </div>
                </div>
            </div>
        </div>
        <ModalDBComponent
            :prompts="promptForDB"
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
import master_data from './master_data'
import { ref, computed, onMounted } from 'vue'
import axios from 'axios'
import draggable from 'vuedraggable'
import './assets/style.scss'
import './assets/promptGenerator.scss'
import HeaderComponent from './components/HeaderComponent.vue'
import ModalDBComponent from './components/ModalDBComponent.vue'
import { colorMulti, colorMono } from './colorVariation'

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
        const displayNsfw = ref<boolean>(false)
        // Hover中のタグ
        const hoverPromptName = ref<string>('')
        // アップロード用プロンプト
        const spellsByUserText = ref<string>('')
        // 手動入力内容
        const manualInputText = ref<string>('')
        // セットされているタグ(プロンプト)のキュー
        const setSpells = ref<{[key: string]: any}[]>([])
        // カラー設定可能なプロンプトのカラーバリエーションと現在の値を格納する配列
        const colorMultiColor = ref<{[key: string]: string}[]>(colorMulti)
        const colorMonochrome = ref<{[key: string]: string}[]>(colorMono)
        const selectedColor = ref<{[key: string]: string}>({})

        // 指定されたタグ名に該当するプロンプトを選択状態にする
        const selectPromptFromSearch = (word: string): void => {
            tagsList.value.map((genre: {[key: string]: any}, i: number) => {
                genre.content.map((prompt: {[key: string]: any}, j: number) => {           
                    if (prompt.tag === word)  tagsList.value[i].content[j].selected = true              
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
                genre['display'] = false
                genre.caption = genre.caption === "" ? "-" : genre.caption

                genre.content.map((prompt: {[key: string]: any}, j: number) => {  
                    prompt['slag'] = prompt.tag.replace(' ', '_')
                    prompt['original_name'] = prompt.tag
                    prompt['original_name_jp'] = prompt.jp
                    prompt['selected'] = false
                    prompt['enhance'] = 0
                    prompt['display'] = !prompt.nsfw || displayNsfw.value ? true : false
                    prompt['parentTag'] = genre.jp
                    prompt['index'] = i + ',' + j                    
                })
                commandList.push(genre)
            })

            return commandList
        }

        // 画面読み込み時にマスタデータ一覧を取得、できなかった場合ローカルのjsファイルから取得
        const getMasterData = async(): Promise<void> => {
            const url = './register/api/getMasterData.php?from=spell_generator'
            await axios.get(url)
                .then(response => {
                    tagsList.value = convertJsonToTagList(response.data)
                })
                .catch(error => {
                    tagsList.value = convertJsonToTagList(master_data)
                    console.log(error)
                })
        }
        
        // nsfwコンテンツの表示設定
        const toggleDisplayNsfw = (): void => {
            tagsList.value.map ((genre: {[key: string]: any}, i: number) => {
                genre.content.map((_: any, j: number) => 
                    tagsList.value[i].content[j]['display'] = 
                        !tagsList.value[i].content[j].nsfw || displayNsfw.value ? true : false
                )
            })
        } 

        // nsfw表示切り替えボタンを押した際の処理
        const displayNsfwContent = (): void => {
            toggleDisplayNsfw()
            setSpells.value.map(prompt => selectPromptFromSearch(prompt.tag))
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
                    parentTag: '手動',
                    detail: '',
                    slag: input.replace(' ', '_'),
                    enhance: enhanceCount,
                    variation: null,
                    index: null,
                    nsfw: false
                })
            }
        }

        // タグ一覧から指定のタグ名を検索し、親タグと日本語名を返す
        const searchTagsFromSpell = (tagname: string, enhanceCount: number): {[key: string]: string | number} => {            
            // カラーリング付プロンプト用の定数。AfterSpaceがプロンプト名本体、BeforSpaceがカラーバリュー。
            const promptAfterSpace: string = tagname.substring(tagname.indexOf(' ')+1)
            console.log(promptAfterSpace)
            const promptBeforeSpace: any = tagname.substring(0, tagname.indexOf(' '))
            const colorTagJP = ref<string>('')
            // カラーバリュー設定が存在する場合プロンプトの日本語名を変更
            if (promptBeforeSpace !== -1) {
                colorMultiColor.value.map(color => {
                    if (promptBeforeSpace === color.prompt) {
                        colorTagJP.value = color.jp
                    }
                })
            }
            // console.log(colorTagJP.value)
            
            const setPrompt: {[key: string]: string | number} = {}
            for (let i = 0; i < tagsList.value.length; i++) {
                for (let j = 0; j < tagsList.value[i].content.length; j++) {
                    const prompt = tagsList.value[i].content[j]
                    console.log(tagname)
                    if (prompt.tag === tagname || (colorTagJP.value.trim() !== '' && promptAfterSpace === prompt.tag)) {
                        if (tagsList.value[i].content[j].variation !== null && colorTagJP.value !== '') {
                            setPrompt['tag'] = promptAfterSpace
                            setPrompt['jp'] = tagsList.value[i].content[j].jp + ' (' + colorTagJP.value + ')'
                        } else {
                            setPrompt['tag'] = tagname
                            setPrompt['jp'] = tagsList.value[i].content[j].jp
                        }
                        setPrompt['original_name'] = tagname
                        setPrompt['original_name_jp'] = tagsList.value[i].content[j].jp
                        setPrompt['parentTag'] = tagsList.value[i].jp
                        setPrompt['detail'] = ''
                        setPrompt['slag'] = tagname.replace(' ', '_')
                        setPrompt['enhance'] = enhanceCount
                        setPrompt['variation'] = tagsList.value[i].content[j].variation
                        setPrompt['index'] = i + ',' + j
                        setPrompt['nsfw'] = tagsList.value[i].content[j].nsfw
                        setPrompt['error'] = ''

                        tagsList.value[i].content[j].selected = true
                        // 該当のプロンプトがnsfwワードだった場合R-18モードにする
                        if (!displayNsfw.value && tagsList.value[i].content[j].nsfw) { 
                            displayNsfw.value = true
                            toggleDisplayNsfw()
                        }
                        return setPrompt
                    } 
                }
            }
            addManualPromptToList(tagname, enhanceCount)
            return {error: 'not found'}
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
                    }
                    const tagname = tag.replace(/{/g, "").replace(/}/g, "").replace(/\[/g, "").replace(/\]/g, "")

                    // 設定プロンプトリストに必要情報を挿入 
                    const searchedTags = searchTagsFromSpell(tagname, enhanceCount.value)
                    if (searchedTags['error'] !== 'not found') setSpells.value.push(searchedTags)        
                }
            })
        }

        // タグのセットキューに挿入
        const toggleSetPromptList = (i: number, j: number): void => { 
            const queue = tagsList.value[i].content[j]
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
            if (colorTag.prompt === 'none') {
                setSpells.value[index].jp = setSpells.value[index].original_name_jp
                setSpells.value[index].tag = setSpells.value[index].original_name
            } else {
                setSpells.value[index].jp = setSpells.value[index].original_name_jp + ' (' + colorTag.jp + ')'
                setSpells.value[index].tag = colorTag.prompt + ' ' + setSpells.value[index].original_name
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

        // タグ(プロンプト)の強化
        const enhanceSpell = (index: number, num: number): void => setSpells.value[index].enhance += num

        // 生成されたNovelAI形式のプロンプト
        const spellsNovelAI = ref('')
        // どのアプリ用に生成するか判定するための配列とインデックス
        const generatedPromptFor = [
            'NovelAI',
            'Stable Diffusion',
        ]
        const promptFor = ref<number>(0)
        const selectedPromptFor = computed(() => generatedPromptFor[promptFor.value])
        // キューにセットされているタグをNovelAIで使える形に変換する
        const convertToNovelAITags = (spells: {[key: string]: any}[]): void => {
            const text = ref('')
            
            spells.map(spell => {
                // タグの付与
                // 強化値が0の場合そのまま追加
                if (spell.enhance === 0) {
                    text.value += spell.tag
                } else if (spell.enhance > 0) {
                    // 強化値が1以上の場合前後に{}を数値分追加
                    if (selectedPromptFor.value === 'Stable Diffusion') {
                        text.value += '('.repeat(spell.enhance) + spell.tag + ')'.repeat(spell.enhance)
                    } else {
                        text.value += '{'.repeat(spell.enhance) + spell.tag + '}'.repeat(spell.enhance)
                    }
                } else if (spell.enhance < 0) {
                    // 強化値が-1以下の場合前後に[]を数値分追加
                    const num = spell.enhance * -1
                    text.value += '['.repeat(num) + spell.tag + ']'.repeat(num)
                }
                text.value += ', '
            })         
            spellsNovelAI.value = text.value
        }

        const togglePromptBrace = (serviceName: string): string => {
            const returnStr = ref<string>('')
            if (serviceName === 'NovelAI') {
                returnStr.value = spellsNovelAI.value.replaceAll(/\(/g, '{').replaceAll(/\)/g, '}')
            } else if (serviceName === 'Stable Diffusion') {
                returnStr.value = spellsNovelAI.value.replaceAll(/\{/g, '(').replaceAll(/\}/g, ')')
            }

            return returnStr.value
        }

        const toggleTagsFor = (): void => {
            if (promptFor.value === generatedPromptFor.length - 1) {
                promptFor.value = 0
            } else {
                promptFor.value += 1
            }
            if (spellsNovelAI.value !== '') spellsNovelAI.value = togglePromptBrace(selectedPromptFor.value)
        }

        // DB保存モーダルの表示可否
        const isOpenSaveModal = ref(false)
        // DB保存用のデータ
        const promptForDB = ref({
            commands: '',
            commands_ban: '',
            description: '',
            seed: '',
            resolution: '',
            others: '',
        })
        // DB保存用のモーダルを開く
        const openSaveModal = (promptList: {[key: string]: any}[]): void => {
            convertToNovelAITags(promptList)
            promptForDB.value.commands = spellsNovelAI.value
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
        // モーダルの表示状態を行進する
        const updateModalState = (isDisplay: boolean): boolean => isOpenSaveModal.value = isDisplay

        const isEditNAIPrompt = ref(false)
        const toggleIsEditNAIPrompt = (state: boolean): boolean => isEditNAIPrompt.value = state
        

        // 画面読み込み時、DBからマスタデータを取得。できない場合はローカルから取得。
        onMounted(() => getMasterData())
        
        return {
            tagsList,
            hoverPromptName,
            setSpells,
            spellsNovelAI,
            copyAlert,
            isOpenSaveModal,
            displayNsfw: displayNsfw,
            manualInput: manualInputText,
            spellsByUser: spellsByUserText,
            promptForDB: promptForDB,
            selectedColor: selectedColor,
            colorMultiColor,
            colorMonochrome,
            selectedPromptFor,
            isEditNAIPrompt: isEditNAIPrompt,
            displayNsfwContent,
            uploadSpell,
            toggleSetPromptList,
            changePromptColor,
            addManualPromptToList,
            enhanceSpell,
            deleteSetPromptList,
            convertToNovelAITags,
            toggleTagsFor,
            openSaveModal,
            copyToClipboard,
            updateAlertText,
            updateModalState,
            toggleIsEditNAIPrompt,
        }
    }
}
</script>