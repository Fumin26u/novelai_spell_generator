import hair from './hair.js'
import baseSpells from './baseSpells.js'
import bodyElements from './bodyElements.js'
import clothes from './clothes.js'
import overall from './overall.js'

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