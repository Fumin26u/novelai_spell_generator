import { createRouter, createWebHashHistory } from 'vue-router'
import PromptGenerator from './PromptGenerator.vue'
import SavedPrompt from './SavedPrompt.vue'
const routes = [
    {
        path: '/', 
        name: 'generator', 
        component: PromptGenerator,
    },
    {
        path: '/saves', 
        name: 'saves',
        component: SavedPrompt,
    },
]

const router = createRouter({
    history: createWebHashHistory(process.env.BASE_URL),
    routes,
})

export default router 


