# novelai_spell_generator
https://novelai.net/ のイラスト自動生成サービスのコマンド生成器です。
製作期間1日の突貫工事なのでコードは環境汚染が発生しそうなレベルでゴミです。

**追記 2022/10/24**
コードはゴミなままですがかなり改良重ねてUI的にも使いやすくなったと思います。(そう信じています)

**追記 2022/11/18**
年齢制限を全年齢、15歳以上、18歳以上の3段階にしました。
プロンプトの年齢制限分類を(僕の独断で)かなり厳格に設定してあるので、誰かに見せる時に事故る可能性は減ったと思います。
現在DBのCRUD処理部分のAPI化を進めています。最終的には全操作をVue側で行い、DB処理のみPHPのAPIで行うようにする予定(目標)です。
~誰か画像登録周りを教えてくれ~

## Project setup
```
npm install
```

### Compiles and hot-reloads for development
```
npm run serve
```

### Compiles and minifies for production
```
npm run build
```

### Lints and fixes files
```
npm run lint
```

### Customize configuration
See [Configuration Reference](https://cli.vuejs.org/config/).
