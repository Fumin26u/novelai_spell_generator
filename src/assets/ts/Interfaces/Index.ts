// 汎用的なType/Interface
export type Nsfw = 'A' | 'B' | 'C' | 'D' | 'Z'

export interface ColorVariation {
    prompt: string,
    jp: string,
}

// Prompt関連のInterface
export interface Prompt {
    id: number,
    tag: string,
    slag: string,
    jp: string,
    parentTag: string,
    display: boolean,
    selected: boolean,
    nsfw: Nsfw,
    variation: null | 'CC' | 'CM',
    index: string | null,
    detail: string | null
}         
export interface SetPrompt extends Prompt {
    output_prompt: string,
    enhance: number,
    color_list: ColorVariation[] | null
}

export interface PromptList {
    slag: string,
    jp: string,
    caption: string | null,
    nsfw: Nsfw,
    display: boolean,
    show_all: boolean,
    content: Prompt[],
}