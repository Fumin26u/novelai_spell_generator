<script setup lang="ts">
import master_data from '@/assets/ts/master_data'
import apiPath from '@/assets/ts/apiPath'
import {
    Nsfw,
    Prompt,
    PromptList,
    SetPrompt,
} from '@/assets/ts/Interfaces/Index'
import { colorMulti, colorMono } from '@/assets/ts/colorVariation'
import { ref, onMounted } from 'vue'
import axios from 'axios'
import '@/assets/scss/promptGenerator.scss'
import HeaderComponent from '@/components/HeaderComponent.vue'
import SetPromptComponent from '@/components/generator/SetPromptComponent.vue'
import ManagePresetComponent from '@/components/ManagePresetComponent.vue'

// 表示するタグ一覧
const masterData = ref<PromptList[]>([])
// nsfwコンテンツの表示可否
const displayNsfw = ref<Nsfw>('A')
// Hover中のタグ
const hoverPromptName = ref<string>('')
// 手動入力内容
const manualInput = ref<string>('')
// セットされているタグ(プロンプト)のキュー
const setPrompt = ref<SetPrompt[]>([])
// アップロード用プロンプト
const uploadPromptInput = ref<string>('')

// 年齢制限表示に応じた個々のプロンプトの表示状態を設定する
const judgeIsDisplay = (limit: string, promptNsfw: string): boolean => {
    switch (limit) {
        case 'A':
            return promptNsfw === 'A' ? true : false
        case 'C':
            return promptNsfw === 'A' || promptNsfw === 'C' ? true : false
        case 'Z':
            return true
        default:
            return promptNsfw === 'A' ? true : false
    }
}
// nsfwの設定に応じた各プロンプトの表示状態を設定
const setIsNsfw = (nsfw: Nsfw, promptList: PromptList[]): PromptList[] => {
    promptList.map((genre: PromptList, i: number) => {
        promptList[i]['display'] = judgeIsDisplay(nsfw, promptList[i].nsfw)
        genre.content.map((_: Prompt, j: number) => {
            promptList[i].content[j]['display'] = judgeIsDisplay(
                nsfw,
                promptList[i].content[j].nsfw
            )
        })
    })

    return promptList
}

// マスタデータをJSオブジェクトの配列に変換
const convertJsonToTagList = (jsonObj: PromptList[]): PromptList[] => {
    const promptListQueue: PromptList[] = []
    Object.keys(jsonObj).map((index: string) =>
        promptListQueue.push(jsonObj[parseInt(index)])
    )

    // 配列に表示に必要なデータを挿入
    const promptList: PromptList[] = []
    promptListQueue.map((genre: PromptList, i: number) => {
        genre.show_all = false
        genre.caption = genre.caption === '' ? '-' : genre.caption

        genre.content.map((prompt: Prompt, j: number) => {
            prompt['slag'] = prompt.tag.replace(' ', '_')
            prompt['selected'] = false
            prompt['parentTag'] = genre.jp
            prompt['index'] = i + ',' + j
        })
        promptList.push(genre)
    })

    return promptList
}

// 年齢制限切り替えボタンを押した際の処理
const toggleDisplayNsfw = (limit: Nsfw): void => {
    displayNsfw.value = limit
    masterData.value = setIsNsfw(displayNsfw.value, masterData.value)
}

// 手動入力でのプロンプトの追加
const addManualPrompt = (input: string, enhanceCount = 0): void => {
    let isAlreadySetPrompt = false
    // 既に追加されているプロンプト名の場合追加しない
    setPrompt.value.map((prompt: SetPrompt) => {
        if (input.trim() === prompt.tag) isAlreadySetPrompt = true
    })
    if (!isAlreadySetPrompt && input.trim() !== '') {
        setPrompt.value.push({
            id: -1,
            tag: input,
            slag: input.replace(' ', '_'),
            jp: input,
            parentTag: '手動',
            display: false,
            selected: false,
            nsfw: 'A',
            variation: null,
            index: null,
            detail: '',
            output_prompt: input,
            enhance: enhanceCount,
            color_list: null,
        })
    }
}

// タグのセットキューに挿入
const toggleSetPromptList = (
    i: number,
    j: number,
    enhanceCount = 0,
    colorTag = '',
    colorTagJP = ''
): void => {
    const queue: SetPrompt = {
        ...masterData.value[i].content[j],
        output_prompt: masterData.value[i].content[j].tag,
        enhance: enhanceCount,
        color_list: null,
    }

    // プロンプト設定リストに存在するタグの場合、そのデータを消去し終了
    const selected = masterData.value[i].content[j].selected
    if (selected) {
        for (let index = 0; index < setPrompt.value.length; index++) {
            if (setPrompt.value[index].tag === queue.tag) {
                setPrompt.value.splice(index, 1)
                masterData.value[i].content[j].selected = false
                return
            }
        }
    }

    // カラーバリエーション設定が存在する場合それに上書き
    switch (masterData.value[i].content[j].variation) {
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
    masterData.value[i].content[j].selected = true
}

// プロンプト一覧から指定されたプロンプト名を検索し、存在する場合プロンプト設定欄にデータを挿入
const searchPrompt = (uploadPromptName: string, enhanceCount: number): void => {
    // アップロードされたプロンプトにスペースが存在するか調べる
    const spaceIndex = uploadPromptName.indexOf(' ')
    const colorTag = uploadPromptName.substring(0, spaceIndex)
    const colorTagJP = ref<string>('')

    // スペースが存在する場合スペース以前の単語がカラータグかどうか調べる
    // 目と髪の色は個別で設定できるようにしたいので除外する
    if (
        colorTag !== '' &&
        uploadPromptName.match(/eyes$/) === null &&
        uploadPromptName.match(/hair$/) === null
    ) {
        colorMulti.map((color) => {
            if (colorTag === color.prompt) colorTagJP.value = color.jp
        })
    }
    // 検索するプロンプト名。カラータグが付いていた場合最初のスペース以後、それ以外の場合引数の値。
    const promptName =
        colorTagJP.value === ''
            ? uploadPromptName
            : uploadPromptName.substring(spaceIndex + 1)

    for (let i = 0; i < masterData.value.length; i++) {
        for (let j = 0; j < masterData.value[i].content.length; j++) {
            const prompt = masterData.value[i].content[j]
            // アップロードされたプロンプト名とリスト内のプロンプトが一致した場合、プロンプト設定のリストにそのプロンプトのデータを挿入
            if (prompt.tag === promptName) {
                toggleSetPromptList(
                    i,
                    j,
                    enhanceCount,
                    colorTag,
                    colorTagJP.value
                )
                // nsfw設定をプロンプトのnsfw設定に上書き
                if (
                    displayNsfw.value === 'A' ||
                    (displayNsfw.value === 'C' && prompt.nsfw === 'Z')
                ) {
                    displayNsfw.value = prompt.nsfw
                }

                masterData.value = setIsNsfw(displayNsfw.value, masterData.value)
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
    masterData.value.map((genre: PromptList, i: number) => {
        genre.content.map((_: Prompt, j: number) => {
            masterData.value[i].content[j].selected = false
        })
    })

    // タグごと配列の要素にする
    const uploadedPromptList = inputPromptList
        .split(',')
        .map((tag) => tag.trim())
    uploadedPromptList.map((prompt: string, index: number) => {
        if (prompt.trim() === '') {
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
            const promptName = prompt
                .replace(/\{/g, '')
                .replace(/\}/g, '')
                .replace(/\[/g, '')
                .replace(/\]/g, '')
                .replace(/\(/g, '')
                .replace(/\)/g, '')

            // プロンプト一覧から指定したプロンプトを検索
            searchPrompt(promptName, enhanceCount.value)
        }
    })
}

// 子コンポーネントから伝えられたプロンプト設定欄の内容を更新
const updateSetPrompt = (childSetPrompt: SetPrompt[]) =>
    (setPrompt.value = childSetPrompt)

// セットキューから指定したプロンプトを削除
const unSelectedPrompt = (promptListIndex: string | null): void => {
    // 送られてきた値がnullの場合プロンプト一覧には存在しないので何もせず終了
    if (promptListIndex === null) return

    // プロンプト一覧から指定されたインデックスのプロンプトの選択を解除
    const tagsIndexList = promptListIndex.split(',')
    const i = parseInt(tagsIndexList[0])
    const j = parseInt(tagsIndexList[1])

    masterData.value[i].content[j].selected = false
}

// DB保存モーダルの表示可否
const isOpenSaveModal = ref<boolean>(false)
// モーダルの表示状態を更新する
const updateModalState = (isDisplay: boolean): boolean =>
    (isOpenSaveModal.value = isDisplay)

const outputPrompt = ref<string>('')
// DB保存用のモーダルを開く
const openSaveModal = (modalState: boolean, output: string): void => {
    outputPrompt.value = output
    updateModalState(modalState)
}

// DBからマスタデータ一覧を取得、できなかった場合ローカルのjsファイルから取得
const getMasterData = async (): Promise<PromptList[]> => {
    const url = apiPath + 'managePrompt.php'
    return await axios
        .get(url)
        .then((response) => {
            // promptList.value = convertJsonToTagList(response.data)
            // setDisplayNsfw(displayNsfw.value)
            return response.data
        })
        .catch((error) => {
            console.log(error)
            return JSON.parse(master_data)
        })
}

// 初期画面表示に必要なデータを作成
const setInitialViewData = async (): Promise<void> => {
    // 取得したマスタデータを表示用の配列に変換し必要情報を追加
    masterData.value = convertJsonToTagList(await getMasterData())
    // nsfwの設定に応じた各プロンプトの表示状態を設定
    setIsNsfw(displayNsfw.value, masterData.value)
}

// 画面読み込み時、ログインユーザーIDを取得し、DBからマスタデータを取得。できない場合はローカルから取得。
onMounted(() => {
    document.title = 'NovelAI プロンプトジェネレーター'
    setInitialViewData()
})
</script>

<template>
    <HeaderComponent></HeaderComponent>
    <main class="prompt-generator">
        <div class="prompt-list">
            <section class="user-setting-area">
                <div class="upload-prompt">
                    <label :id="'upload-prompt'"
                        >プロンプトをアップロード</label
                    >
                    <input
                        type="text"
                        :id="'upload-prompt'"
                        v-model="uploadPromptInput"
                        @keyup.enter="uploadPrompt(uploadPromptInput)"
                    />
                    <button
                        @click="uploadPrompt(uploadPromptInput)"
                        class="btn-common green"
                    >
                        アップロード
                    </button>
                </div>
                <div class="toggle-nsfw">
                    <button
                        @click="toggleDisplayNsfw('C')"
                        v-show="displayNsfw === 'A'"
                        class="btn-common blue"
                    >
                        全年齢
                    </button>
                    <button
                        @click="toggleDisplayNsfw('Z')"
                        v-show="displayNsfw === 'C'"
                        class="btn-common green"
                    >
                        R-15
                    </button>
                    <button
                        @click="toggleDisplayNsfw('A')"
                        v-show="displayNsfw === 'Z'"
                        class="btn-common pink"
                    >
                        R-18
                    </button>
                </div>
            </section>
            <section class="prompt-list-area">
                <div
                    class="prompt-list-genre"
                    v-for="(genre, i) in masterData"
                    :key="genre.slag"
                    :style="[genre.display ? 'display:block' : 'display:none']"
                >
                    <div class="description">
                        <div>
                            <p class="genre">{{ genre.jp }}</p>
                            <p class="caption">{{ genre.caption }}</p>
                        </div>
                        <div>
                            <span
                                @click="masterData[i]['show_all'] = true"
                                v-if="!masterData[i]['show_all']"
                                >▼</span
                            >
                            <span
                                @click="masterData[i]['show_all'] = false"
                                v-if="masterData[i]['show_all']"
                                >▲</span
                            >
                        </div>
                    </div>
                    <div
                        :style="[
                            masterData[i]['show_all']
                                ? 'max-height:none;'
                                : 'max-height:240px;',
                        ]"
                        class="prompt-list-prompt"
                    >
                        <div
                            v-for="(prompt, j) in genre.content"
                            :key="prompt.slag"
                            :style="[
                                prompt.display
                                    ? 'display:block'
                                    : 'display:none',
                            ]"
                        >
                            <button
                                :class="[
                                    prompt.selected
                                        ? 'btn-toggle selected'
                                        : 'btn-toggle',
                                    'nsfw_' + prompt.nsfw,
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
        @updateModalState="updateModalState"
        :style="[isOpenSaveModal ? 'display: block' : 'display: none']"
    />
    <router-view></router-view>
</template>
