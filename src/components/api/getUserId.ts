import ApiManager from './apiManager'
import apiPath from '@/assets/ts/apiPath'
// ログインユーザーIDを取得する
const apiManager = new ApiManager()
const getUserInfo = async () => {
    const url = apiPath + 'manageAccount.php'
    const response = await apiManager.post(url, {
        method: 'getUserData',
    })
    return response.user_id
}
const user_id = await getUserInfo()
export default await user_id
