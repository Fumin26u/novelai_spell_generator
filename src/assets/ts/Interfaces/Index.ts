// 汎用的なType/Interface
export type Nsfw = 'A' | 'B' | 'C' | 'D' | 'Z'
export type NsfwDisplay = '全年齢' | 'R-12' | 'R-15' | 'R-17' | 'R-18'

export interface ColorVariation {
    prompt: string
    jp: string
}

// Prompt関連のInterface
export interface Prompt {
    id: number
    tag: string
    slag: string
    jp: string
    parentTag: string
    display: boolean
    selected: boolean
    nsfw: Nsfw
    variation: null | 'CC' | 'CM'
    index: string | null
    detail: string | null
}
export interface SetPrompt extends Prompt {
    output_prompt: string
    enhance: number
    color_list: ColorVariation[] | null
}

export interface PromptList {
    slag: string
    jp: string
    caption: string | null
    nsfw: Nsfw
    display: boolean
    show_all: boolean
    content: Prompt[]
}

// Preset関連のInterface
export interface Preset {
    preset_id: number | null
    image: string | ArrayBuffer | null
    imagePath: string | null
    from: string
    commands: string
    commands_ban: string
    description: string | null
    nsfw: Nsfw
    seed: string
    resolution_width: string | number | null
    resolution_height: string | number | null
    resolution: string | null
    model: string | null
    sampling: number | null
    sampling_algo: string | null
    scale: number | null
    options: any
    others: string
}
export interface PresetDetail extends Preset {
    index: number
    thumbnail: string
    nsfw_display: NsfwDisplay
}

export interface SearchData {
    method: 'search'
    age: string[]
    item: string[]
    word: string
    sort: string
    order: string
}

export interface AccountInfo {
    method: string
    email: string
    user_id: string
    password: string
}

// マスタデータ関連
export interface MasterPrompt {
    detail: string | null
    id: number
    identifier: 'prompt'
    jp: string
    nsfw: Nsfw
    nsfw_display: NsfwDisplay
    tag: string
    variation: string | null
    variation_display: 'なし' | 'マルチカラー' | 'モノクロ'
}

export interface MasterData {
    caption: string | null
    content: MasterPrompt[]
    id: number,
    identifier: 'genre'
    jp: string
    nsfw: Nsfw
    nsfw_display: NsfwDisplay
    show_prompt: boolean
    slag: string
}
