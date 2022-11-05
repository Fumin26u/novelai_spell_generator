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
                            <button @click="displayNsfw = displayNsfw ? false:true, toggleDisplayNsfw()" :class="[displayNsfw ? 'btn-common pink': 'btn-common blue']">
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
                                    <span @click="tagsList[i]['display'] = true" :style="[tagsList[i]['display'] ? 'display:none' : 'display:block;']">▼</span>
                                    <span @click="tagsList[i]['display'] = false" :style="[tagsList[i]['display'] ? 'display:block' : 'display:none;']">▲</span>
                                </div>
                            </div>
                            <div :style="[tagsList[i]['display'] ? 'max-height:none;' : 'max-height:240px;']">
                                <div 
                                    v-for="(prompt, j) in genre.content" 
                                    :key="prompt.slag" 
                                    :style="[prompt.display ? 'display:block; position:relative;' : 'display:none']">
                                    <button 
                                        :class="[prompt.selected ? 'btn-toggle selected' : 'btn-toggle']" 
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
                        <p>選択中のプロンプト：{{hoverPromptName}}</p>
                        <h2>設定プロンプト一覧</h2>
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
                                    <div v-if="element.variation === 'CM'">
                                        <span class="caption">色の設定</span>
                                        <select 
                                            :style="[element.nsfw ? 'color:tomato;' : 'color:blue;']" 
                                            v-model="selectedColor" 
                                            @change="changePromptColor(selectedColor, index)"
                                        >
                                            <option v-for="color in colorMonochrome" :key="color.prompt" :value="color.prompt">{{ color.jp }}</option>
                                        </select>
                                    </div>
                                    <div v-if="element.variation === 'CC'">
                                        <span class="caption">色の設定</span>
                                        <select 
                                            :style="[element.nsfw ? 'color:tomato;' : 'color:blue;']" 
                                            v-model="selectedColor" 
                                            @change="changePromptColor(selectedColor, index)"
                                        >
                                            <option v-for="color in colorMultiColor" :key="color.prompt" :value="color.prompt">{{ color.jp }}</option>
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
                            <p class="output">出力値: 
                                <span v-if="spellsNovelAI.value !== undifined">
                                    {{ spellsNovelAI.value + manualInput }}
                                </span>
                            </p>
                            <label :for="'manual-input'">手動入力</label>
                            <input type="text" :id="'manual-input'" v-model="manualInput">
                        </div>
                        <div class="button-area">                          
                            <button @click="convertToNovelAITags(setSpells)" class="btn-common add">呪文生成</button>
                            <button @click="copyToClipboard(spellsNovelAI.value + manualInput)" class="btn-common copy">コピー</button>
                            <button @click="openSaveModal(setSpells), isOpenSaveModal = true" class="btn-common blue">保存</button>
                            <span class="copy-alert">{{ copyAlert }}</span>           
                        </div>
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

<script>
import master_data from './master_data.js'
import { ref, onMounted } from 'vue'
import axios from 'axios'
import draggable from 'vuedraggable'
import './assets/style.scss'
import HeaderComponent from './components/HeaderComponent.vue'
import ModalDBComponent from './components/ModalDBComponent.vue'
import { colorMulti, colorMono } from './colorVariation.js'

export default {
    components: {
        draggable,
        HeaderComponent,
        ModalDBComponent,
    },
    setup() {
        // 表示するタグ一覧
        const tagsList = ref([])
        // nsfwコンテンツの表示可否
        const displayNsfw = ref(false)
        // Hover中のタグ
        const hoverPromptName = ref('')
        // アップロード用プロンプト
        const spellsByUserText = ref('')
        // 手動入力内容
        const manualInputText = ref('')
        // セットされているタグ(プロンプト)のキュー
        const setSpells = ref([])
        // カラー設定可能なプロンプトのカラーバリエーション
        const colorMultiColor = ref(colorMulti)
        const colorMonochrome = ref(colorMono)

        // 指定されたタグ名に該当するプロンプトを選択状態にする
        const selectPromptFromSearch = (word) => {
            tagsList.value.map((genre, i) => {
                genre.content.map((prompt, j) => {           
                    if (prompt.tag === word)  tagsList.value[i].content[j].selected = true              
                })
            })
        }

        // JSON文字列にしたマスタデータをJSオブジェクトの配列に変換
        const convertJsonToTagList = (json) => {
            const jsonObj = typeof json === 'string' ? JSON.parse(json) : json
            const commandListQueue = []
            Object.keys(jsonObj).map(index => commandListQueue.push(jsonObj[index]))

            // 配列に表示に必要なデータを挿入
            const commandList = []
            commandListQueue.map((genre, i) => {
                genre['display'] = false
                genre.caption = genre.caption === "" ? "-" : genre.caption

                genre.content.map((prompt, j) => {  
                    prompt['slag'] = prompt.tag.replace(' ', '_')
                    prompt['original_name'] = prompt.tag
                    prompt['selected'] = false
                    prompt['enhance'] = 0
                    prompt['display'] = !prompt.nsfw || (prompt.nsfw && displayNsfw.value) ? true : false
                    prompt['parentTag'] = genre.jp
                    prompt['index'] = i + ',' + j                    
                })
                commandList.push(genre)
            })

            return commandList
        }

        // 画面読み込み時にマスタデータ一覧を取得、できなかった場合ローカルのjsファイルから取得
        const getMasterData = async() => {
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
        onMounted(() => getMasterData())
        
        // nsfwコンテンツの表示設定
        const toggleDisplayNsfw = () => {
            tagsList.value.map ((genre, i) => {
                genre.content.map((_, j) => 
                    tagsList.value[i].content[j]['display'] = 
                        !tagsList.value[i].content[j].nsfw || (tagsList.value[i].content[j].nsfw && displayNsfw.value) 
                        ? true : false)
            })
            setSpells.value.map(prompt => selectPromptFromSearch(prompt.tag))
        } 

        // タグ一覧から指定のタグ名を検索し、親タグと日本語名を返す
        const searchTagsFromSpell = (word) => {
            const retVal = ref([])
            tagsList.value.map((genre, i) => {
                genre.content.map((prompt, j) => {           
                    if (prompt.tag === word) {
                        retVal.value.push(tagsList.value[i].jp)
                        retVal.value.push(tagsList.value[i].content[j].jp)
                        retVal.value.push(tagsList.value[i].content[j].variation)
                        retVal.value.push(i + ',' + j)
                        retVal.value.push(tagsList.value[i].content[j].nsfw)
                        // 該当のプロンプトがnsfwワードだった場合R-18モードにする
                        if (!displayNsfw.value && tagsList.value[i].content[j].nsfw) { 
                            displayNsfw.value = true
                            toggleDisplayNsfw()
                        }
                    }               
                })
            })
            return retVal.value
        }

        // 既存のタグがアップロードされた場合、セットキューに対象値を追加
        const uploadSpell = spell => {
            if (spell.trim() === '') return
            // 既存の設定プロンプトリストと手動入力欄をリセット
            setSpells.value = []
            manualInputText.value = ''
            tagsList.value.map((genre, i) => {
                genre.content.map((_, j) => {
                    tagsList.value[i].content[j].selected = false
                })
            })

            // タグごと配列の要素にする
            const tagsQueue = spell.split(',')
            const tags = tagsQueue.map(tag => tag.trim())
            tags.map((tag, index) => {
                if(tag.trim() === " " || tag.trim() === "") {
                    tags.splice(index, 1)
                } else {
                    const spellQueue = {}
                    // 文字の前後に{}または[]がある場合、その数分強化値を追加する
                    const enhanceCount = ref(0)
                    if (tag.match(/\{/g)) {
                        enhanceCount.value = tag.match(/\{/g || []).length
                    } else if (tag.match(/\[/g)) {
                        enhanceCount.value = tag.match(/\[/g || []).length * -1 
                    }
                    const tagname = tag.replace(/{/g, "").replace(/}/g, "").replace(/\[/g, "").replace(/\]/g, "")

                    // 親タグ、日本語名、各インデックスを取得
                    const [parentTag, tagjp, variation, index, nsfw] = searchTagsFromSpell(tagname)
                    // 該当のプロンプトを選択状態にする
                    selectPromptFromSearch(tagname)
                    // 親タグらを取得時点でそれらがundefinedの場合、そのタグを手入力欄に代わりに挿入
                    if (parentTag === undefined || tagjp === undefined || index === undefined) {
                        manualInputText.value += tag + ', '
                    } else {
                        spellQueue['tag'] = tagname
                        spellQueue['jp'] = tagjp
                        spellQueue['detail'] = ''
                        spellQueue['slag'] = tagname.replace(' ', '_')
                        spellQueue['parentTag'] = parentTag
                        spellQueue['enhance'] = enhanceCount.value
                        spellQueue['variation'] = variation
                        spellQueue['index'] = index
                        spellQueue['nsfw'] = nsfw
                        setSpells.value.push(spellQueue)
                    }
                }
            })
        }

        // タグのセットキューに挿入
        const toggleSetPromptList = (i, j) => { 
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
        const changePromptColor = (colorTag, index) => setSpells.value[index].tag = colorTag + ' ' + setSpells.value[index].original_name

        // セットキューから指定したプロンプトを削除
        const deleteSetPromptList = (index) => {
            const tagsIndexList = setSpells.value[index].index.split(',')
            const i = parseInt(tagsIndexList[0])
            const j = parseInt(tagsIndexList[1])
            
            tagsList.value[i].content[j].selected = false
            setSpells.value.splice(index, 1)
        }

        // タグ(プロンプト)の強化
        const enhanceSpell = (index, num) => setSpells.value[index].enhance += num

        // 生成されたNovelAI形式のプロンプト
        const spellsNovelAI = ref('')
        // キューにセットされているタグをNovelAIで使える形に変換する
        const convertToNovelAITags = spells => {
            const text = ref('')
            
            spells.map(spell => {
                // タグの付与
                // 強化値が0の場合そのまま追加
                if (spell.enhance === 0) {
                    text.value += spell.tag
                } else if (spell.enhance > 0) {
                    // 強化値が1以上の場合前後に{}を数値分追加
                    text.value += '{'.repeat(spell.enhance) + spell.tag + '}'.repeat(spell.enhance)
                } else if (spell.enhance < 0) {
                    // 強化値が-1以下の場合前後に[]を数値分追加
                    const num = spell.enhance * -1
                    text.value += '['.repeat(num) + spell.tag + ']'.repeat(num)
                }
                text.value += ', '
            })
            
            spellsNovelAI.value = text
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
        const openSaveModal = (setSpells) => {
            convertToNovelAITags(setSpells)
            promptForDB.value.commands = spellsNovelAI.value
        }
        
        // プロンプトをコピーした際のアラート
        const copyAlert = ref('')
        // プロンプトをクリップボードにコピーする
        const copyToClipboard = text => {
            navigator.clipboard.writeText(text)
            copyAlert.value = 'クリップボードにコピーしました。'
        }

        // コンポーネントから受け取ったアラートテキストを更新する
        const updateAlertText = text => copyAlert.value = text
        // モーダルの表示状態を行進する
        const updateModalState = isDisplay => isOpenSaveModal.value = isDisplay
        
        return {
            tagsList,
            hoverPromptName,
            setSpells,
            options: {
                animation: 200
            },
            spellsNovelAI,
            copyAlert,
            isOpenSaveModal,
            displayNsfw: displayNsfw,
            manualInput: manualInputText,
            spellsByUser: spellsByUserText,
            promptForDB: promptForDB,
            selectedColor: '',
            colorMultiColor,
            colorMonochrome,
            toggleDisplayNsfw,
            uploadSpell,
            toggleSetPromptList,
            changePromptColor,
            enhanceSpell,
            deleteSetPromptList,
            convertToNovelAITags,
            openSaveModal,
            copyToClipboard,
            updateAlertText,
            updateModalState,
        }
    }
}
</script>
<style lang="scss" scoped>
.content {
    position: relative;
    display: flex;
    justify-content: space-between;
    > .main-content {
        width: 68%;
        border-right: 1px solid #888;
    }
    > .spell-settings {
        width: 32%;
    }
}

.upload-prompt {
    display: flex;
    justify-content: space-between;
    input {
        width: 500px;
        margin: 0 8px;
        padding: 4px 0;
        font-size: 16px;
    }
    > .toggle-nsfw {
        margin-right: 8px;
        > .btn-common {
            font-family: 'Yu Gothic Medium';
            letter-spacing: 1px;
            font-size: 15px;
            font-weight: bold;
        }
        > .btn-common.pink {
            border: 1.8px solid tomato;
            color: tomato;
            &:hover {
                background: tomato;
                color: white;
            }
        }
    }
}

.tag-list {
    display: flex;
    justify-content: space-between;
    flex-wrap: wrap;
    align-items: flex-start;
    margin-right: 0.5em;
}

.spell-list {
    width: 17%;
    padding: 8px;
    margin: 26px 0 1em;
    border: 1px dashed #888;
    box-shadow: 0 2px 2px #aaa;
    > .description {
        display: flex;
        justify-content: space-between;
        > div {
            display: block;
            &:first-child {
                width: 90%;
            }
            &:last-child {
                width: 10%;
            }
        }
        > div span {
            cursor: pointer;
            user-select: none;
        }
    }
    .genre {
        font-weight: bold;
        font-size: 18px;
    }
    .caption {
        font-size: 14px;
    }
    > div {
        max-height: 240px;
        overflow-y: auto;
        > div {
            margin: 8px 0;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        > div span {
            margin-right: 8px;
            max-width: 120px;
        }
    }
}

.spell-settings {
    margin: 0 8px;
    position: sticky;
    top: 50px;
    height: 96vh;
    > .description {
        h2 {
            margin: 0;
            font-size: 20px;
        }
        p {
            font-size: 13px;
        }          
    }
    > .spells {
        max-height: 560px;
        overflow-y: scroll;
        border-bottom: 1px solid #888;
        > div {
            margin: 4px auto;
            display: flex;
            justify-content: space-evenly;
            align-items: center;
            > .prompt-variation-select{
                width: 65%;
                display: flex;
                justify-content: space-between;
                > div {
                    margin: 0 16px 0 auto;
                }
                > div.prompt-name {
                    margin: 0;
                    display: flex;
                    justify-content: space-evenly;
                    align-items: center;
                    &:before {
                        content: '';
                        margin-right: 8px;
                        width: 18px;
                        height: 18px;
                        cursor: pointer;
                        background-image: url('./assets/images/dnd.png');
                        background-size: contain;
                        background-repeat: no-repeat;
                        background-position: center;
                    }
                    p {
                        font-weight: bold;
                    }
                }
                > div .caption {
                    font-size: 13px;
                    margin-left: 12px;
                    margin-bottom: -6px;
                    display: block;
                }
                > .prompt-name .caption {
                    margin-left: 0;
                }
                > div > select {
                    width: 86px;
                    margin-left: 8px;
                    font-family: 'Yu Gothic Medium';
                    outline: none;
                    border: none;
                    border-bottom: 1px solid #888;
                    font-size: 16px;
                    white-space: normal;
                }
            }
            > .enhance-area {
                width: 25%;
            }
            > .enhance-area span {
                display: inline-block;
                width: 33%;    
                text-align: center;
            }
            > .delete-area {
                width: 10%;
            }
        }
    }
    > .output-area {
        position: absolute;
        bottom: 40px;
        div p {
            font-size: 13px;
        }
        #manual-input {
            margin: 0 8px; 
            padding: 8px; 
            width: 380px;
            font-size: 15px;
        }
        .output {
            margin: 0 0 8px;
        }
        > .button-area {
            margin: 8px 0;
            button {
                display: inline-block;
                vertical-align: middle;
                margin-right: 8px;
                font-weight: bold;
            }
        }
    }
}
</style>
