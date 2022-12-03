// ローカルの場合Xamppを使用しているので絶対パスでAPIのURIを取得
const apiPath =
    location.origin + location.pathname === 'http://localhost:8080/'
        ? 'http://localhost/novelai_spell_generator/api/'
        : './api/'

export default apiPath
