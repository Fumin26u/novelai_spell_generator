<template>
    <div class="top-content">
        <h1 :style="'margin-right: 48px; display: inline-block;'"><a href="https://novelai.net/image">NovelAI</a> コマンドジェネレーター</h1>
        <a href="https://fuminsv.sakura.ne.jp/spellGenerator/register/" target="_blank" :style="'font-weight:bold; font-size: 18px;'">コマンド登録</a>
        <div class="content">
            <div class="main-content">
                <section class="upload-prompt">
                    <label :id="'upload-prompt'">コマンドをアップロード</label>
                    <input type="text" :id="'upload-prompt'" v-model="spellsByUser">
                    <button @click="uploadSpell(spellsByUser)">アップロード</button>
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
                                <div v-for="(spells, k) in tag.content" :key="spells.slag">
                                    <span>{{ spells.jp }}</span>
                                    <button class="btn-common add" v-if="!spells.selected" @click="addSetSpells(i, j, k)">追加</button>
                                    <button class="btn-common delete" v-if="spells.selected" @click="deleteSetSpells(i, j, k)">削除</button>
                                </div>
                            </div>
                        </div>
                    </div>
                </section>
            </div>
            <div class="spell-settings">
                <h2>設定コマンド一覧</h2>
                <div class="spells">
                    <div v-for="(spell, index) in setSpells" :key="spell.slag">
                        <p><span :style="'font-weight:bold; margin-right:8px'">{{ spell.parentTag }}</span>{{ spell.jp }}</p>
                        <div class="enhance-area">
                            <button @click="enhanceSpell(index, -1)" class="btn-common delete">－</button>
                            <span>{{ spell.enhance }}</span>
                            <button @click="enhanceSpell(index, 1)" class="btn-common add">＋</button>
                        </div>
                        <div class="setOrder-area">
                            <div>
                                <button @click="setSpellOrder(index, index-1, 'up')" class="btn-common order">▲</button>
                                <button @click="setSpellOrder(index, index+1, 'down')" class="btn-common order">▼</button>
                            </div>
                            <div>
                                <button @click="setSpellOrder(index, 0, 'top')" class="btn-common order">top</button>
                                <button @click="setSpellOrder(index, setSpells.length-1, 'bottom')" class="btn-common order">bottom</button>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="output-area">
                    <div>
                        <label :for="'manual-input'">手動入力</label>
                        <input type="text" :id="'manual-input'" :style="'margin: 0 8px; padding: 8px; width:320px;'" v-model="manualInput">
                    </div>
                    <div :style="'margin: 1em 0'">
                        <button @click="convertToNovelAITags(setSpells)" class="btn-common add">コマンドを生成</button>
                        <p :style="'display: inline-block; margin: 8px 0;'">出力値: 
                            <span v-if="spellsNovelAI.value !== undifined">
                                {{ spellsNovelAI.value + manualInput }}
                            </span>
                        </p>
                        <button 
                            @click="copyToClipboard(spellsNovelAI.value + manualInput)"
                            :style="'display: block;'"
                            class="btn-common copy"
                        >
                            コピー
                        </button>
                        <span class="copy-alert">{{ copyAlert }}</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
import master_data from './master_data.js'
import { ref } from 'vue'

export default {
    components: {},
    setup() {
        // 表示するタグ一覧
        const tagsList = ref([])
        // セットされているタグ(コマンド)のキュー
        const setSpells = ref([])
        // 生成されたNovelAI形式のコマンド
        const spellsNovelAI = ref('')
        // コマンドをコピーした際のアラート
        const copyAlert = ref('')
        // 手動入力内容
        const manualInputText = ref('')
        // アップロード用コマンド
        const spellsByUserText = ref('')

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
            commandList.map(category => {
                category.content.map(genre => {
                    genre['display'] = false 
                    genre.content.map(tag => {
                        tag['slag'] = tag.tag.replace(' ', '_')
                        tag['enhance'] = 0
                        tag['selected'] = false
                        tag['parentTag'] = genre.jp
                    })              
                })
            })

            return commandList
        }
        tagsList.value = convertJsonToTagList(master_data)
        console.log(tagsList.value)

        // タグ一覧から指定のタグ名を検索し、親タグと日本語名を返す
        const searchTagsFromSpell = (word) => {
            const retVal = ref([])
            tagsList.value.map((tags, i) => {
                tags.content.map((tag, j) => {
                    tag.content.map((spell, k) => {
                        if(spell.tag === word) {
                            retVal.value.push(tagsList.value[i].content[j].jp)
                            retVal.value.push(tagsList.value[i].content[j].content[k].jp)
                            tagsList.value[i].content[j].content[k].selected = true
                        }
                    })
                })
            })
            return retVal.value
        }

        // 既存のタグがアップロードされた場合、セットキューに対象値を追加
        const uploadSpell = (spell) => {
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

                    // 親タグと日本語名を取得
                    const [parentTag, tagjp] = searchTagsFromSpell(tagname)

                    spellQueue['tag'] = tagname
                    spellQueue['jp'] = tagjp
                    spellQueue['detail'] = ''
                    spellQueue['slag'] = tagname.replace(' ', '_')
                    spellQueue['selected'] = true
                    spellQueue['parentTag'] = parentTag
                    spellQueue['enhance'] = enhanceCount.value

                    setSpells.value.push(spellQueue)
                }
            })
        }

        // タグのセットキューに挿入
        const addSetSpells = (i, j, k) => { 
            const queue = tagsList.value[i].content[j].content[k]

            if (!setSpells.value.includes(queue)) {
                setSpells.value.push(queue)
                tagsList.value[i].content[j].content[k].selected = true
            }
        }

        // タグの削除
        const deleteSetSpells = (i, j, k) => {
            const queue = tagsList.value[i].content[j].content[k]
            for (let index = 0; index < setSpells.value.length; index++) {
                if (setSpells.value[index].tag === queue.tag) {
                    setSpells.value.splice(index, 1)
                    tagsList.value[i].content[j].content[k].selected = false
                }
            }
        }

        // タグ(コマンド)の強化
        const enhanceSpell = (index, num) => {
            setSpells.value[index].enhance += num
        }
        // タグの順序変更
        const setSpellOrder = (index, order, method) => {
            if (((index === 0 && order === -1) || (index === setSpells.value.length - 1 && order === 1))) return
            if (method === 'up' || method === 'down') {
                [setSpells.value[index], setSpells.value[order]] = [setSpells.value[order], setSpells.value[index]]
            } else if (method === 'top' || method === 'bottom') {
                const obj = setSpells.value.splice(index, 1)
                if (method === 'top') setSpells.value.unshift(obj[0])
                if (method === 'bottom') setSpells.value.push(obj[0])
            }
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

        
        // コマンドをクリップボードにコピーする
        const copyToClipboard = text => {
            navigator.clipboard.writeText(text)
            copyAlert.value = 'クリップボードにコピーしました。'
        }
        
        return {
            tagsList,
            setSpells,
            spellsNovelAI,
            copyAlert,
            manualInput: manualInputText,
            spellsByUser: spellsByUserText,
            uploadSpell,
            addSetSpells,
            enhanceSpell,
            setSpellOrder,
            deleteSetSpells,
            convertToNovelAITags,
            copyToClipboard,
        }
    }
}
</script>
<style lang="scss" scoped>
.top-content {
    font-family: 'Yu Gothic Medium';
    max-width: 1560px;
    margin: 0 auto;
}

h1, h2, h3 {
    margin: 8px;
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
.btn-common.order {
    border: 1px solid darkblue;
    color: darkblue;
    font-size: 12px;
    padding: 2px 8px;
    &:hover {
        background-color: darkblue;
        color: white;
    }
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

.upload-prompt > input {
    width: 600px;
    margin: 0 8px;
    padding: 4px 0;
}

.tag-list {
    display: flex;
    justify-content: space-around;
}

.spell-list {
    padding: 16px;
    border: 1px solid #888;
    border-radius: 8px;
    box-shadow: 2px 2px 8px #888;
    > div {
        max-height: 180px;
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
    height: 90vh;
    > .spells {
        max-height: 480px;
        overflow-y: scroll;
        border-bottom: 1px solid #888;
        > div {
            margin: 4px auto;
            display: flex;
            justify-content: space-evenly;
            align-items: center;
            > p {
                width: 55%;
            }
            > .enhance-area {
                width: 25%;
            }
            > .enhance-area span {
                display: inline-block;
                width: 33%;    
                text-align: center;
            }
            > .setOrder-area {
                width: 20%;
                display: flex;
                align-items: center;
                justify-content: space-around;
            }
            > .setOrder-area button {
                display: block;
            }
            > .setOrder-area div:nth-child(2) button {
                width: 55px;
            }
        }
    }
    > .output-area {
        position: absolute;
        bottom: 0;
        > p, button {
            display: inline-block;
        }
        div p {
            font-size: 13px;
        }

        .copy-alert {
            margin-left: 8px;
        }
    }
}
</style>
