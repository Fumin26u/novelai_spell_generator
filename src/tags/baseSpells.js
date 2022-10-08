const baseSpells = {
    jp: '基本設定',
    slag: 'baseSpells',
    type: '',
    content: [
        {
            jp: '品質',
            slag: 'quality',
            type: 'checkbox',
            content: [
                {
                    tag: 'masterpiece',
                    jp: '高解像度',
                    detail: '',
                },
                {
                    tag: 'best quality',
                    jp: '最高品質',
                    detail: '',
                },
                {
                    tag: 'high quality',
                    jp: '高品質',
                    detail: '',
                },
                {
                    tag: 'white background',
                    jp: '白背景',
                    detail: '',
                },
                {
                    tag: 'game cg',
                    jp: 'ゲームCG風',
                    detail: '',
                },
                {
                    tag: 'official art',
                    jp: '公式絵風',
                    detail: '',
                },
            ]
        },
        {
            jp: 'R-18',
            slag: 'r18',
            type: 'checkbox',
            content: [
                {
                    tag: 'nsfw',
                    jp: 'nsfw',
                    detail: '',
                },
                {
                    tag: 'hetero',
                    jp: '異性愛',
                    detail: '',
                },
                {
                    tag: 'nude',
                    jp: '裸',
                    detail: '',
                },
                {
                    tag: 'wet sweat',
                    jp: '汗で濡れている',
                    detail: '',
                },
                {
                    tag: 'assertive_female',
                    jp: '積極的な女性',
                    detail: '',
                },
            ]
        },
        {
            jp: '性別',
            slag: 'gender',
            type: 'select',
            content: [
                {
                    tag: '1girl',
                    jp: '女性(1人)',
                    detail: '',
                },
                {
                    tag: '1boy',
                    jp: '男性(1人)',
                    detail: '',
                },
            ]
        },
        {
            jp: '人数',
            slag: 'peoples',
            type: 'select',
            content: [
                {
                    tag: 'solo',
                    jp: '1人',
                    detail: '',
                },
            ]
        },
        // {
        //     jp: '年齢',
        //     slag: 'age',
        //     type: 'number',
        // },
    ]
}

export default baseSpells