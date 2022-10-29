<template>
    <div class="top-content">
        <div class="main">
            <div class="title-area">
                <h1 :style="'margin-right: 48px; display: inline-block;'"><a href="https://novelai.net/image">NovelAI</a> プロンプトジェネレーター</h1>
                <div class="link-area">
                    <a href="https://nai-pg.com/register/" target="_blank" class="prompt-saver">プロンプトセーバー</a>
                    <a href="https://nai-pg.com/register/t/terms_of_use.php" target="_blank">利用規約</a>
                    <a href="https://nai-pg.com/register/t/privacy_policy.php" target="_blank">プライバシーポリシー</a>
                </div>
            </div>
            <div class="content">
                <div class="main-content">
                    <section class="upload-prompt">
                        <label :id="'upload-prompt'">プロンプトをアップロード</label>
                        <input type="text" :id="'upload-prompt'" v-model="spellsByUser">
                        <button @click="uploadSpell(spellsByUser)" class="btn-common add">アップロード</button>
                    </section>
                    <section v-for="(tags, i) in tagsList" :key="tags.slag">
                        <h2>{{ tags.jp }}</h2>
                        <div class="tag-list">
                            <div 
                                class="spell-list"
                                v-for="(tag, j) in tags.content"
                                :key="tag.slag"
                            >
                                <p :style="'font-weight:bold'">{{ tag.jp }}</p>
                                <div>
                                    <div v-for="(spell, k) in tag.content" :key="spell.slag" :style="'position: relative;'">
                                        <button 
                                            :class="[spell.selected ? 'btn-toggle selected' : 'btn-toggle']" 
                                            @click="toggleSetPromptList(i, j, k)"
                                            @mouseover="hoverPromptName = spell.tag"
                                            @mouseleave="hoverPromptName = ''"
                                        >
                                        {{ spell.jp }}
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </section>
                </div>
                <div class="spell-settings">
                    <p>選択中のプロンプト：{{hoverPromptName}}</p>
                    <h2>設定プロンプト一覧</h2>
                    <draggable 
                        class="spells" 
                        v-model="setSpells"
                        item-key="index"
                        @end="displaySetSpells()"
                    >
                        <template #item="{element, index}">
                            <div>
                                <p><span :style="'font-weight:bold; margin-right:8px'">{{ element.parentTag }}</span>{{ element.jp }}</p>
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
                        <div>
                            <label :for="'manual-input'">手動入力</label>
                            <input type="text" :id="'manual-input'" v-model="manualInput">
                        </div>
                        <div :style="'margin: 1em 0'">
                            <button @click="convertToNovelAITags(setSpells)" class="btn-common add">プロンプトを生成</button>
                            <p :style="'display: inline-block; margin: 8px 0;'">出力値: 
                                <span v-if="spellsNovelAI.value !== undifined">
                                    {{ spellsNovelAI.value + manualInput }}
                                </span>
                            </p>
                            <div>
                                <button 
                                    @click="copyToClipboard(spellsNovelAI.value + manualInput)"
                                    :style="'display: inline-block;'"
                                    class="btn-common copy"
                                >
                                    コピー
                                </button>
                                <button 
                                    @click="openSaveModal(setSpells), isOpenSaveModal = true" 
                                    class="btn-common blue" 
                                    :style="'display: inline-block; margin-left: 8px;'"
                                >
                                    保存
                                </button>
                            </div>
                            <span class="copy-alert">{{ copyAlert }}</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="modal-cover" :style="[isOpenSaveModal ? 'display: block' : 'display: none']" @click="isOpenSaveModal = false"></div>
        <div class="modal-window" :style="[isOpenSaveModal ? 'display: block' : 'display: none']">
            <div>
                <h3>データをDBに登録</h3>
                <small>※<a href="https://nai-pg.com/register/" target="_blank" :style="'font-weight: bold;'">プロンプトセーバー</a>でのログインが必要です。非ログイン時は登録ボタンを押しても登録されません。</small>
            </div>
            <div class="close-modal">
                <span @click="isOpenSaveModal = false" class="btn-close"></span>
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
                        <select v-model="promptForDB.resolution">
                            <option disabled value="">以下の項目から選択</option>
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

<script>
import master_data from './master_data.js'
import { ref } from 'vue'
import draggable from 'vuedraggable'
import axios from 'axios'

export default {
    components: { draggable },
    setup() {
        // 表示するタグ一覧
        const tagsList = ref([])
        // Hover中のタグ
        const hoverPromptName = ref('')
        // セットされているタグ(プロンプト)のキュー
        const setSpells = ref([])
        // 生成されたNovelAI形式のプロンプト
        const spellsNovelAI = ref('')
        // プロンプトをコピーした際のアラート
        const copyAlert = ref('')
        // 手動入力内容
        const manualInputText = ref('')
        // アップロード用プロンプト
        const spellsByUserText = ref('')
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

        const displaySetSpells = () => {
            console.log(setSpells.value)
        }

        // JSON文字列にしたマスタデータをJSオブジェクトの配列に変換
        const convertJsonToTagList = (json) => {
            const jsonObj = JSON.parse(json)
            const commandList = []
            Object.keys(jsonObj).map(index => commandList.push(jsonObj[index]))

            // 内部のオブジェクトとなっているコンテンツを配列に変換
            commandList.map(category => {
                const categoryContent = []
                Object.keys(category.content).map(i => {
                    const genreContent = []
                    Object.keys(category.content[i].content).map(j => {
                        genreContent.push(category.content[i].content[j])
                    })

                    category.content[i].content = genreContent
                    categoryContent.push(category.content[i])
                })
                category.content = categoryContent
            })

            // 配列に表示に必要なデータを挿入
            commandList.map((category, i) => {
                category.content.map((genre, j) => {
                    genre['display'] = false 
                    genre.content.map((tag, k) => {
                        tag['slag'] = tag.tag.replace(' ', '_')
                        tag['enhance'] = 0
                        tag['selected'] = false
                        tag['parentTag'] = genre.jp
                        tag['index'] = i + ',' + j + ',' + k
                    })              
                })
            })

            return commandList
        }
        tagsList.value = convertJsonToTagList(master_data)

        // タグ一覧から指定のタグ名を検索し、親タグと日本語名を返す
        const searchTagsFromSpell = (word) => {
            const retVal = ref([])
            tagsList.value.map((tags, i) => {
                tags.content.map((tag, j) => {
                    tag.content.map((spell, k) => {
                        if (spell.tag === word) {
                            retVal.value.push(tagsList.value[i].content[j].jp)
                            retVal.value.push(tagsList.value[i].content[j].content[k].jp)
                            retVal.value.push(i + ',' + j + ',' + k)
                            tagsList.value[i].content[j].content[k].selected = true
                        } 
                    })
                })
            })
            return retVal.value
        }

        // 既存のタグがアップロードされた場合、セットキューに対象値を追加
        const uploadSpell = (spell) => {
            // 既存の設定プロンプトリストと手動入力欄をリセット
            setSpells.value = []
            manualInputText.value = ''
            tagsList.value.map((tags, i) => {
                tags.content.map((tag, j) => {
                    tag.content.map((_, k) => {
                        tagsList.value[i].content[j].content[k].selected = false
                    })
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
                    const [parentTag, tagjp, index] = searchTagsFromSpell(tagname)
                    // 親タグらを取得時点でそれらがundefinedの場合、そのタグを手入力欄に代わりに挿入
                    if (parentTag === undefined || tagjp === undefined || index === undefined) {
                        manualInputText.value += tag + ', '
                    } else {
                        spellQueue['tag'] = tagname
                        spellQueue['jp'] = tagjp
                        spellQueue['detail'] = ''
                        spellQueue['slag'] = tagname.replace(' ', '_')
                        spellQueue['selected'] = true
                        spellQueue['parentTag'] = parentTag
                        spellQueue['enhance'] = enhanceCount.value
                        spellQueue['index'] = index
    
                        setSpells.value.push(spellQueue)
                    }
                }
            })
        }

        // タグのセットキューに挿入
        const toggleSetPromptList = (i, j, k) => { 
            const queue = tagsList.value[i].content[j].content[k]
            const selected = tagsList.value[i].content[j].content[k].selected

            if (!selected) {
                if (!setSpells.value.includes(queue)) {
                    setSpells.value.push(queue)
                    tagsList.value[i].content[j].content[k].selected = true
                }
            } else {
                for (let index = 0; index < setSpells.value.length; index++) {
                    if (setSpells.value[index].tag === queue.tag) {
                        setSpells.value.splice(index, 1)
                        tagsList.value[i].content[j].content[k].selected = false
                    }
                }
            }
        }

        const deleteSetPromptList = (index) => {
            const tagsIndexList = setSpells.value[index].index.split(',')
            const i = parseInt(tagsIndexList[0])
            const j = parseInt(tagsIndexList[1])
            const k = parseInt(tagsIndexList[2])
            
            tagsList.value[i].content[j].content[k].selected = false
            setSpells.value.splice(index, 1)
        }

        // タグ(プロンプト)の強化
        const enhanceSpell = (index, num) => {
            setSpells.value[index].enhance += num
        }

        // キューにセットされているタグをNovelAIで使える形に変換する
        const convertToNovelAITags = (spells) => {
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

        // DB保存用のモーダルを開く
        const openSaveModal = (setSpells) => {
            convertToNovelAITags(setSpells)
            promptForDB.value.commands = spellsNovelAI.value
        }
        
        // プロンプトをクリップボードにコピーする
        const copyToClipboard = text => {
            navigator.clipboard.writeText(text)
            copyAlert.value = 'クリップボードにコピーしました。'
        }

        // プロンプトをDBに保存する
        const savePrompt = (promptForDB) => {
            if (promptForDB.commands === '') {
                copyAlert.value = 'コマンドが入力されていません。'
                isOpenSaveModal.value = false
                return
            }
            if (typeof promptForDB.seed === 'number') {
                copyAlert.value = 'Seed値が数値で入力されていません。'
                isOpenSaveModal.value = false
                return
            }

            const url = 'https://nai-pg.com/register/api.php'
            axios.post(url, promptForDB).catch(error => console.log(error))
            copyAlert.value = 'プロンプトをデータベースに登録しました。'
            isOpenSaveModal.value = false
        }
        
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
            manualInput: manualInputText,
            spellsByUser: spellsByUserText,
            promptForDB: promptForDB,
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
            displaySetSpells,
            uploadSpell,
            toggleSetPromptList,
            enhanceSpell,
            deleteSetPromptList,
            convertToNovelAITags,
            openSaveModal,
            copyToClipboard,
            savePrompt,
        }
    }
}
</script>
<style lang="scss" scoped>
.top-content {
    font-family: 'Yu Gothic Medium';
    box-sizing: border-box;
    position: relative;
    > .main {
        max-width: 1560px;
        margin: 0 auto;
    }
}

a {
    text-decoration: none;
    &:hover {
        text-decoration: underline;
    }
}

h1, h2, h3 {
    margin: 8px 0;
    padding: 0;
}
h1 {font-size: 24px;}
h2 {font-size: 22px;}
ul, p {
    margin: 0;
    padding: 0;
}

input {
    padding: 4px;
}
input[type="checkbox"], input[type='radio'] {
    transform: scale(1.1);
}

button, input[type="submit"] {
    cursor: pointer;
}
.btn-common {
    background-color: white;
    border-radius: 4px;
    padding: 4px 8px;
    transition: all .1s;
    cursor: pointer;
}
.btn-common.add {
    border: 1.5px solid green;
    color: green;
    &:hover {
        background-color: green;
        color: white;
    }
}
.btn-common.delete {
    border: 1.5px solid red;
    color: red;
    &:hover {
        background-color: red;
        color: white;
    }
}
.btn-common.copy {
    border: 1.8px solid darkorange;
    color: darkorange;
    font-weight: bold;
    &:hover {
        background-color: darkorange;
        color: white;
    }
}
.btn-common.blue {
    border: 1.8px solid darkblue;
    color: darkblue;
    font-weight: bold;
    &:hover {
        background-color: darkblue;
        color: white;
    }
}

.btn-toggle {
    font-family: 'Yu Gothic Medium', '游ゴシック Medium', sans-serif;
    border: none;
    outline: none;
    width: 144px;
    font-size: 16px;
    padding: 4px;
    margin: 2px 6px 2px 0;
    background: hsl(196, 61%, 88%);
    color: hsl(196, 100%, 10%);
    box-shadow: 2px 2px hsl(196, 100%, 40%);
    transition: all 0.03s;
}
.btn-toggle.selected {
    padding: 4px 8px;
    background: hsl(196, 100%, 15%);
    color: white;
    box-shadow: inset 3px 3px 3px hsl(196, 100%, 5%);
    border: none;
    transform: translate(2px, 2px);
}
.display-prompt-name {
    display: block;
    position: absolute;
    width: 100%;
    top: 0;
    left: -100%;
    background-color: #00ffff;
    z-index: 100;
}

.title-area {
    border-bottom: 2px dashed #888;
    padding: 0.5em 0;
    margin: 0 0 1em;
}
.title-area > h1, .title-area > .link-area {
    display: inline-block;
    vertical-align: center;
    margin-right: 3em;
}
.title-area > .link-area > a {
    margin: 0 1em;
}
.title-area > .link-area .prompt-saver {
    font-size: 18px;
    font-weight: bold;
}

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

.upload-prompt > * {
    display: inline-block;
    vertical-align: middle;
}
.upload-prompt > input {
    width: 600px;
    margin: 0 8px;
    padding: 4px 0;
    font-size: 16px;
}

.tag-list {
    display: flex;
    justify-content: space-between;
    margin-right: 0.5em;
}

.spell-list {
    padding: 16px 16px 8px;
    border: 1px dashed #888;
    /* border-radius: 8px; */
    box-shadow: 0 2px 2px #aaa;
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
    > .spells {
        max-height: 520px;
        overflow-y: scroll;
        border-bottom: 1px solid #888;
        > div {
            margin: 8px auto;
            display: flex;
            justify-content: space-evenly;
            align-items: center;
            > p {
                width: 65%;
                &::before {
                    content: '';
                    margin-right: 8px;
                    display: inline-block;
                    vertical-align: middle;
                    width: 18px;
                    height: 18px;
                    cursor: pointer;
                    background-image: url('./dnd.png');
                    background-size: contain;
                    background-repeat: no-repeat;
                    background-position: center;
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
        > p, button {
            display: inline-block;
        }
        div p {
            font-size: 13px;
        }
        #manual-input {
            margin: 0 8px; 
            padding: 8px; 
            width: 380px;
            font-size: 15px;
        }

        .copy-alert {
            margin-left: 8px;
        }
    }
}

.modal-cover {
    width: 100%;
    background: rgba(0,0,0,0.5);
    z-index: 90;
    position: absolute;
    top: 0;
    bottom: 0;
}
.modal-window {
    position: absolute;
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
.btn-close {
    outline: none;
    border: none;
    cursor: pointer;
    background: #dedede;
    border-radius: 24px;
    width: 40px;
    height: 40px;
    position: absolute;
    top: 8px;
    right: 8px;
    transition: all .1s;
    &:hover {
        background: #ccc;
    }
    &:before, &:after {
        content: "";
        position: absolute;
        top: 50%;
        left: 50%;
        width: 3px;
        height: 30px;
        background: #666;
    }
    &:before {
        transform: translate(-50%, -50%) rotate(45deg);
    }
    &:after {
        transform: translate(-50%, -50%) rotate(-45deg);
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
