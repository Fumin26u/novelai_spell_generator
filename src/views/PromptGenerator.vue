<script setup lang="ts">
import master_data from '@/assets/ts/master_data'
import apiPath from '@/assets/ts/apiPath'
import {
    MasterData,
    Nsfw,
    Prompt,
    PromptList,
    SetPrompt,
} from '@/assets/ts/Interfaces/Index'
import { colorMulti, colorMono } from '@/assets/ts/colorVariation'
import { ref, onMounted, watch } from 'vue'
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
            nsfw: 'A',
            variation: null,
            output_prompt: input,
            enhance: enhanceCount,
            color_list: null,
        })
    }
}

// プロンプト設定一覧に追加するデータを作成
const createSetPrompt = (
    prompt: Prompt,
    enhanceCount: number = 0,
    colorTag: string = '',
    colorTagJP: string = ''
) => {
    // プロンプトを選択した場合データ追加
    const insertPromptData: SetPrompt = {
        ...prompt,
        output_prompt: prompt.tag,
        enhance: enhanceCount,
        color_list: null,
    }

    // カラーバリエーション設定が存在する場合それに上書き
    switch (prompt.variation) {
        case 'CC':
            insertPromptData['color_list'] = colorMulti
            break
        case 'CM':
            insertPromptData['color_list'] = colorMono
            break
        default:
            insertPromptData['color_list'] = null
            break
    }

    // カラータグが存在する場合はタグ名と表示名を上書き
    if (colorTag !== '' && colorTagJP !== '') {
        insertPromptData.output_prompt = colorTag + ' ' + insertPromptData.tag
        insertPromptData.jp = insertPromptData.jp + ' (' + colorTagJP + ')'
    }

    return insertPromptData
}

// プロンプト設定一覧のデータ変更処理
const toggleSetPromptList = (i: number, j: number) => {
    const prompt = masterData.value[i].content[j]

    // プロンプトの選択を解除した場合一覧からデータ削除
    if (prompt.selected) {
        const removeIndex = setPrompt.value.findIndex(
            (p) => p.tag === prompt.tag
        )
        setPrompt.value.splice(removeIndex, 1)
        prompt.selected = false
        return
    }

    setPrompt.value.push(createSetPrompt(prompt))
    prompt.selected = true
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

    let promptIndex = -1
    let genreIndex = -1
    for (let i = 0; i < masterData.value.length; i++) {
        promptIndex = masterData.value[i].content.findIndex(
            (prompt) => prompt.tag === promptName
        )

        if (promptIndex !== -1) {
            genreIndex = i
            break
        }
    }

    // リスト内のプロンプトと1つも合致しなかった場合、手動入力として扱う
    if (promptIndex === -1) {
        addManualPrompt(uploadPromptName, enhanceCount)
        return
    }

    // 合致した場合、プロンプト設定一覧に該当プロンプトを挿入
    const prompt = masterData.value[genreIndex].content[promptIndex]
    setPrompt.value.push(
        createSetPrompt(prompt, enhanceCount, colorTag, colorTagJP.value)
    )

    prompt.selected = true
    // nsfw設定をプロンプトのnsfw設定に上書き
    if (
        displayNsfw.value === 'A' ||
        (displayNsfw.value === 'C' && prompt.nsfw === 'Z')
    ) {
        displayNsfw.value = prompt.nsfw
    }
    return
}

// 既存のタグがアップロードされた場合、セットキューに対象値を追加
const uploadPrompt = (inputPromptList: string): void => {
    const st = performance.now()
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
    const ed = performance.now()
    console.log(ed - st)
}

// nsfw設定を監視し変更があった場合プロンプトの表示状態を設定する
watch(displayNsfw, () => {
    setIsNsfw(displayNsfw.value, masterData.value)
})

// 子コンポーネントから伝えられたプロンプト設定欄の内容を更新
const updateSetPrompt = (childSetPrompt: SetPrompt[]) =>
    (setPrompt.value = childSetPrompt)

// セットキューから指定したプロンプトを削除
const unSelectedPrompt = (promptListIndex?: string): void => {
    // 送られてきた値がnullの場合プロンプト一覧には存在しないので何もせず終了
    if (promptListIndex === undefined) return

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
            return response.data
        })
        .catch((error) => {
            console.log(error)
            return JSON.parse(master_data)
        })
}

// 初期画面表示に必要なデータを作成
const getInitialViewData = async (): Promise<PromptList[]> => {
    // 取得したマスタデータを表示用の配列に変換し必要情報を追加
    return setIsNsfw(
        displayNsfw.value,
        convertJsonToTagList(await getMasterData())
    )
}

// 画面読み込み時、ログインユーザーIDを取得し、DBからマスタデータを取得。できない場合はローカルから取得。
onMounted(async () => {
    document.title = 'NovelAI プロンプトジェネレーター'
    masterData.value = await getInitialViewData()
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
