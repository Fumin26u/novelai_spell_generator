import hair from './tags/hair.js'
import baseSpells from './tags/baseSpells.js'
import bodyElements from './tags/bodyElements.js'
import clothes from './tags/clothes.js'
import overall from './tags/overall.js'

const tagsListQueue = [
    baseSpells,
    overall,
    bodyElements,
    clothes,
    hair,
]

tagsListQueue.map(tags => {
    tags.content.map(tag => {
        tag['display'] = false
        if (tag.type !== 'number') {
            tag.content.map(tagname => {
                tagname['slag'] = tagname.tag.replace(' ', '_')
                tagname['enhance'] = 0
                tagname['selected'] = false
                tagname['parentTag'] = tag.jp
            })
        }
    })
})

export default tagsListQueue