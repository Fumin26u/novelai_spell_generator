<template>
    <HeaderComponent @getUserInfo="getUserInfo"></HeaderComponent>
    <main class="prompt-generator">
        <div class="prompt-list">
            <section class="user-setting-area">
                <div class="upload-prompt">
                    <label :id="'upload-prompt'">プロンプトをアップロード</label>
                    <input type="text" :id="'upload-prompt'" v-model="uploadPromptInput" @keyup.enter="uploadPrompt(uploadPromptInput)">
                    <button @click="uploadPrompt(uploadPromptInput)" class="btn-common green">アップロード</button>
                </div>
                <div class="toggle-nsfw">
                    <button @click="toggleDisplayNsfw('C')" v-show="displayNsfw === 'A'" class="btn-common blue">全年齢</button>
                    <button @click="toggleDisplayNsfw('Z')" v-show="displayNsfw === 'C'" class="btn-common green">R-15</button>
                    <button @click="toggleDisplayNsfw('A')" v-show="displayNsfw === 'Z'" class="btn-common pink">R-18</button>            
                </div>
            </section>
            <section class="prompt-list-area">
                <div 
                    class="prompt-list-genre"
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
                    <div :style="[promptList[i]['show_all'] ? 'max-height:none;' : 'max-height:240px;']" class="prompt-list-prompt">
                        <div 
                            v-for="(prompt, j) in genre.content" 
                            :key="prompt.slag" 
                            :style="[prompt.display ? 'display:block' : 'display:none']">
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
            @unSelectedPrompt="unSelectedPrompt"
            @openSaveModal="openSaveModal"
        />
    </main>
    <ManagePresetComponent
        id="generator"
        :prompts="outputPrompt"
        :displayModalState="isOpenSaveModal"
        @updateModal="updateModalState"
        :style="[isOpenSaveModal ? 'display: block' : 'display: none']"
    />
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
import ManagePresetComponent from './components/ManagePresetComponent.vue'

export default {
    components: {
        HeaderComponent,
        SetPromptComponent,
        ManagePresetComponent,
    },
    setup() {
        // Type Annotation
        type Nsfw = 'A' | 'B' | 'C' | 'D' | 'Z'

        interface ColorVariation {
            prompt: string,
            jp: string,
        }

        interface Prompt {
            id: number,
            tag: string,
            slag: string,
            jp: string,
            parentTag: string,
            display: boolean,
            selected: boolean,
            nsfw: Nsfw,
            variation: null | 'CC' | 'CM',
            index: string,
            detail: string | null
        }         
        interface PromptQueue extends Prompt {
            output_prompt: string,
            enhance: number,
            color_list: ColorVariation[] | null
        }
        
        interface PromptList {
            slag: string,
            jp: string,
            caption: string | null,
            nsfw: Nsfw,
            display: boolean,
            show_all: boolean,
            content: Prompt[],
        }
        // 表示するタグ一覧
        const promptList = ref<PromptList[]>([])
        // nsfwコンテンツの表示可否
        const displayNsfw = ref<Nsfw>('A')
        // Hover中のタグ
        const hoverPromptName = ref<string>('')
        // アップロード用プロンプト
        const uploadPromptInput = ref<string>('')
        // 手動入力内容
        const manualInput = ref<string>('')
        // セットされているタグ(プロンプト)のキュー
        const setPrompt = ref<{[key: string]: any}[]>([])
        // カラー設定可能なプロンプトの選択されたカラーを格納する配列
        const selectedColor = ref<{[key: string]: string}>({})

        // 年齢制限表示に応じた個々のプロンプトの表示状態を設定する
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
            promptList.value.map ((genre: PromptList, i: number) => {
                promptList.value[i]['display'] = judgeIsDisplay(limit, promptList.value[i].nsfw)
                genre.content.map((_: Prompt, j: number) => {
                    promptList.value[i].content[j]['display'] = judgeIsDisplay(limit, promptList.value[i].content[j].nsfw)
                })
            })
        } 

        // JSON文字列にしたマスタデータをJSオブジェクトの配列に変換
        const convertJsonToTagList = (jsonObj: PromptList[]): PromptList[] => {
            const commandListQueue: PromptList[] = []
            Object.keys(jsonObj).map((index: string) => commandListQueue.push(jsonObj[parseInt(index)]))

            // 配列に表示に必要なデータを挿入
            const commandList: PromptList[] = []
            commandListQueue.map((genre: PromptList, i: number) => {
                genre.show_all = false
                genre.caption = genre.caption === "" ? "-" : genre.caption

                genre.content.map((prompt: Prompt, j: number) => {  
                    prompt['slag'] = prompt.tag.replace(' ', '_')
                    prompt['selected'] = false
                    prompt['parentTag'] = genre.jp
                    prompt['index'] = i + ',' + j                    
                })
                commandList.push(genre)
            })

            return commandList
        }

        // 指定されたタグ名に該当するプロンプトを選択状態にする
        const selectPromptFromSearch = (word: string): void => {
            promptList.value.map((genre: PromptList, i: number) => {
                genre.content.map((prompt: Prompt, j: number) => {           
                    if (prompt.tag === word)  promptList.value[i].content[j].selected = true              
                })
            })
        }
        
        // 年齢制限切り替えボタンを押した際の処理
        const toggleDisplayNsfw = (limit: Nsfw): void => {
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
        const toggleSetPromptList = (
            i: number, 
            j: number, 
            enhanceCount: number = 0, 
            colorTag: string = '',
            colorTagJP: string = ''
        ): void => { 
            const queue: PromptQueue = {
                ...promptList.value[i].content[j],
                output_prompt: promptList.value[i].content[j].tag,
                enhance: enhanceCount,
                color_list: null,
            }
            
            // プロンプト設定リストに存在するタグの場合、そのデータを消去し終了
            const selected = promptList.value[i].content[j].selected
            if (selected) {
                for (let index = 0; index < setPrompt.value.length; index++) {
                    if (setPrompt.value[index].tag === queue.tag) {
                        setPrompt.value.splice(index, 1)
                        promptList.value[i].content[j].selected = false
                        return
                    }
                }
            }

            // カラーバリエーション設定が存在する場合それに上書き
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

            // カラータグが存在する場合はタグ名と表示名を上書き
            if (colorTag !== '' && colorTagJP !== '') {
                queue.output_prompt = colorTag + ' ' + queue.tag
                queue.jp = queue.jp + ' (' + colorTagJP + ')'
            }

            setPrompt.value.push(queue)
            promptList.value[i].content[j].selected = true   
        }

        // プロンプト一覧から指定されたプロンプト名を検索し、存在する場合プロンプト設定欄にデータを挿入
        const searchPrompt = (uploadPromptName: string, enhanceCount: number): void => {            
            // アップロードされたプロンプトにスペースが存在するか調べる
            const spaceIndex = uploadPromptName.indexOf(' ')
            const colorTag = uploadPromptName.substring(0, spaceIndex)
            const colorTagJP = ref<string>('')
            
            // スペースが存在する場合スペース以前の単語がカラータグかどうか調べる
            if (colorTag !== '') {
                colorMulti.map(color => {
                    if (colorTag === color.prompt) colorTagJP.value = color.jp
                })
            } 
            // 検索するプロンプト名。カラータグが付いていた場合最初のスペース以後、それ以外の場合引数の値。
            const promptName = colorTagJP.value === '' ? uploadPromptName : uploadPromptName.substring(spaceIndex + 1)
            
            for (let i = 0; i < promptList.value.length; i++) {
                for (let j = 0; j < promptList.value[i].content.length; j++) {
                    
                    const prompt = promptList.value[i].content[j]
                    // アップロードされたプロンプト名とリスト内のプロンプトが一致した場合、プロンプト設定のリストにそのプロンプトのデータを挿入
                    if (prompt.tag === promptName) {
                        toggleSetPromptList(i, j, enhanceCount, colorTag, colorTagJP.value)
                        // nsfw設定をプロンプトのnsfw設定に上書き
                        if (displayNsfw.value === 'A' || (displayNsfw.value === 'C' && prompt.nsfw === 'Z')) {
                            displayNsfw.value = prompt.nsfw
                        } 
                        setDisplayNsfw(displayNsfw.value)
                        return
                    }
                }
            }
            // リスト内のプロンプトと1つも合致しなかった場合、手動入力として扱う
            addManualPrompt(uploadPromptName, enhanceCount)
            return
        }

        // 既存のタグがアップロードされた場合、セットキューに対象値を追加
        const uploadPrompt = (inputPromptList: string): void => {
            if (inputPromptList.trim() === '') return
            // 既存の設定プロンプトリストと手動入力欄をリセット
            setPrompt.value = []
            manualInput.value = ''
            promptList.value.map((genre: {[key: string]: any}, i: number) => {
                genre.content.map((_: any, j: number) => {
                    promptList.value[i].content[j].selected = false
                })
            })

            // タグごと配列の要素にする
            const uploadedPromptList = inputPromptList.split(',').map(tag => tag.trim())
            uploadedPromptList.map((prompt: string, index: number) => {
                if(prompt.trim() === "") {
                    uploadedPromptList.splice(index, 1)
                } else {
                    // 文字の前後に{}または[]がある場合、その数分強化値を追加する
                    const enhanceCount = ref(0)
                    if (prompt.match(/\{/g) !== null) {
                        enhanceCount.value = prompt.match(/\{/g)!.length
                    } else if (prompt.match(/\[/g) !== null) {
                        enhanceCount.value = prompt.match(/\[/g)!.length * -1 
                    } else if (prompt.match(/\(/g) !== null) {
                        enhanceCount.value = prompt.match(/\(/g)!.length
                    } 
                    const promptName = prompt.replace(/\{/g, "").replace(/\}/g, "").replace(/\[/g, "").replace(/\]/g, "").replace(/\(/g, "").replace(/\)/g, "")

                    // プロンプト一覧から指定したプロンプトを検索
                    searchPrompt(promptName, enhanceCount.value)      
                }
            })
        }

        // 子コンポーネントから伝えられたプロンプト設定欄の内容を更新
        const updateSetPrompt = (childSetPrompt: {[key: string]: any}[]) => setPrompt.value = childSetPrompt
        
        // セットキューから指定したプロンプトを削除
        const unSelectedPrompt = (promptListIndex: string): void => {
            const tagsIndexList = promptListIndex.split(',')
            const i = parseInt(tagsIndexList[0])
            const j = parseInt(tagsIndexList[1])
            
            promptList.value[i].content[j].selected = false
        }

        // DB保存モーダルの表示可否
        const isOpenSaveModal = ref<boolean>(false)
        // モーダルの表示状態を更新する
        const updateModalState = (isDisplay: boolean): boolean => isOpenSaveModal.value = isDisplay

        const outputPrompt = ref<string>('')
        // DB保存用のモーダルを開く
        const openSaveModal = (modalState: boolean, output: string): void => {
            outputPrompt.value = output
            updateModalState(modalState)
        }
        
        // DBからマスタデータ一覧を取得、できなかった場合ローカルのjsファイルから取得
        const getMasterData = async(): Promise<void> => {
            const url = registerPath + 'api/getMasterData.php?from=spell_generator'
            await axios.get(url)
                .then(response => { 
                    promptList.value = convertJsonToTagList(response.data)
                    setDisplayNsfw(displayNsfw.value)
                })
                .catch(error => {
                    promptList.value = convertJsonToTagList(JSON.parse(master_data))
                    setDisplayNsfw(displayNsfw.value)
                    console.log(error)
                })
        }

        // ログインユーザーIDを取得
        const user_id = ref<string>('')
        const getUserInfo = (userId: string) => user_id.value = userId; 

        // 画面読み込み時、ログインユーザーIDを取得し、DBからマスタデータを取得。できない場合はローカルから取得。
        onMounted(() => {
            document.title = 'NovelAI プロンプトジェネレーター'
            getMasterData()
        })
        
        return {
            promptList,
            hoverPromptName,
            setPrompt,
            isOpenSaveModal,
            displayNsfw: displayNsfw,
            manualInput: manualInput,
            uploadPromptInput: uploadPromptInput,
            selectedColor: selectedColor,
            outputPrompt: outputPrompt,
            uploadPrompt,
            toggleSetPromptList,
            toggleDisplayNsfw,
            addManualPrompt,
            updateSetPrompt,
            unSelectedPrompt,
            openSaveModal,
            updateModalState,
            getUserInfo,
        }
    }
}
</script>