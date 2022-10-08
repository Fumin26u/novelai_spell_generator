<template>
    <div class="top-content">
        <h1><a href="https://novelai.net/image">NovelAI</a> コマンドジェネレーター</h1>
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
                                <!-- <div v-if="tag.type === 'select'">
                                    <div v-for="(spells, k) in tag.content" :key="spells.slag">
                                        <input 
                                            type="radio" 
                                            :id="spells.slag"
                                            :name="tag.slag"
                                            @click="addSetSpells(i, j, k, tag.type)">
                                        <label :for="spells.slag">{{ spells.jp }}</label>
                                    </div>
                                </div> -->
                                <!-- <div v-if="tag.type === 'checkbox'"> -->
                                <div v-for="(spells, k) in tag.content" :key="spells.slag">
                                    <span>{{ spells.jp }}</span>
                                    <button class="btn-common add" v-if="!spells.selected" @click="addSetSpells(i, j, k, tag.type)">追加</button>
                                    <button class="btn-common delete" v-if="spells.selected" @click="deleteSetSpells(i, j, k)">削除</button>
                                </div>
                                <!-- </div> -->
                                <div v-if="tag.type === 'number'">
                                    <button @click="adjustAge(-1)">－</button>
                                    <input 
                                        v-model.number="age"
                                        :id="tag.slag"
                                        min="0"
                                        :style="'width: 32px;'"
                                    >
                                    <label>歳</label>
                                    <button @click="adjustAge(1)">＋</button>
                                    <br>
                                    <button @click="addSetSpells(i, j, 0, tag.type, age)">追加</button>
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
import tagsListQueue from './tagsList.js'
import { ref } from 'vue'

export default {
    components: {},
    setup() {
        // 表示するタグ一覧
        const tagsList = ref(tagsListQueue)
        // セットされているタグ(コマンド)のキュー
        const setSpells = ref([])
        // 生成されたNovelAI形式のコマンド
        const spellsNovelAI = ref('')
        // コマンドをコピーした際のアラート
        const copyAlert = ref('')
        // 年齢タグ用
        const ageNum = ref(15)
        // 手動入力内容
        const manualInputText = ref('')
        // アップロード用コマンド
        const spellsByUserText = ref('')

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
                    const tagname = tag.replace(/{/g, "").replace(/}/g, "").replace(/\[/g, "").replace(/\]/, "")

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
        const addSetSpells = (i, j, k, type = 'checkbox', age = 0) => { 
            if (type === 'number') {
                setSpells.value.map((spell, index) => {
                    if(spell.tag.match(/years old/)) {
                        setSpells.value.splice(index, 1)
                    }
                })
                setSpells.value.push({
                    tag: age + ' years old',
                    jp: age + '歳',
                    detail: '',
                    parentTag: '年齢',
                    enhance: 0
                })
                return
            }
            const queue = tagsList.value[i].content[j].content[k]

            if (!setSpells.value.includes(queue)) {
                setSpells.value.push(queue)
                tagsList.value[i].content[j].content[k].selected = true
            }
        }

        // タグの削除
        const deleteSetSpells = (i, j, k) => {
            console.log(i, j, k)
            console.log(setSpells.value)
            console.log(tagsList.value[i].content[j].content[k])
            const queue = tagsList.value[i].content[j].content[k]
            for (let index = 0; index < setSpells.value.length; index++) {
                if (setSpells.value[index].tag === queue.tag) {
                    setSpells.value.splice(index, 1)
                    tagsList.value[i].content[j].content[k].selected = false
                }
            }
            // if (setSpells.value.includes(queue)) {
            //     console.log('aaa')
            //     setSpells.value.splice(setSpells.value.indexOf(queue), 1)
            //     tagsList.value[i].content[j].content[k].selected = false
            // }
        }


        // タグ(コマンド)の強化
        const enhanceSpell = (index, num) => {
            setSpells.value[index].enhance += num
        }
        // 年齢の調整
        const adjustAge = num => {
            ageNum.value += num
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
        
        // タグのセットキューが変化した際、コマンドを生成する
        // watchEffect(() => {
        //     spellsNovelAI.value = convertToNovelAITags(setSpells.value).value
        // })
        
        return {
            tagsList,
            setSpells,
            spellsNovelAI,
            copyAlert,
            age: ageNum,
            manualInput: manualInputText,
            spellsByUser: spellsByUserText,
            uploadSpell,
            adjustAge,
            addSetSpells,
            enhanceSpell,
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

.content {
    position: relative;
    display: flex;
    justify-content: space-between;
    > .main-content {
        width: 72%;
        border-right: 1px solid #888;
    }
    > .spell-settings {
        width: 28%;
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
            > p {
                width: 60%;
            }
            > div {
                width: 30%;
            }
            > button {
                width: 10%;
            }
            > .enhance-area span {
                display: inline-block;
                width: 40px;
                text-align: center;
            }
        }
    }
    > .output-area {
        position: absolute;
        bottom: 0;
        > p, button {
            display: inline-block;
        }
        .copy-alert {
            margin-left: 8px;
        }
    }
}
</style>
