-- phpMyAdmin SQL Dump
-- version 4.9.5
-- https://www.phpmyadmin.net/
--
-- ホスト: mysql714.db.sakura.ne.jp
-- 生成日時: 2022 年 11 月 24 日 23:41
-- サーバのバージョン： 5.7.37-log
-- PHP のバージョン: 7.2.34

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET AUTOCOMMIT = 0;
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- データベース: `fuminsv_spell_generator`
--

--
-- テーブルのデータのダンプ `preset`
--

INSERT INTO `preset` (`preset_id`, `user_id`, `commands`, `commands_ban`, `description`, `nsfw`, `image`, `seed`, `resolution`, `others`, `created_at`, `updated_at`) VALUES
(4, 'Fumiya0719', '{{masterpiece}}, {{best quality}}, {{ultra-detailed}}, {{illustration}}, solo, 1girl, light green hair, blue eyes, medium hair, ', 'public hair,{{extra fingers}},extra arms,extra legs,{{troubled eyebrows}},cheek,worst quality,raised_eyebrows, error, missing fingers, extra digit, fewer digits, cropped, worst quality, {blurry}, {{mutated hands and fingers}}, mutation duplicate, extra arms, extra ears, extra eyes, extra legs, extra thighs, fused digit, fused face, fused feet, {fused fingers}, {fused hairs}, {fused hands},fused mouth, {fused thigh gap},{long body}, {missing arm}, missing digit, missing ears, missing thigh gap, missing thighs, {one hand with less than 5 digit}, {one hand with less than 5 fingers}, {one hand with more than 5 digit}, {one hand with more than 5 fingers}, {poorly drawn}, thick lips, unnatural body', '緑髪碧眼テンプレート', 'A', NULL, '', 'Portrait (Normal) 512x768', '', '2022-10-12 13:08:20', '2022-11-06 03:55:30'),
(5, 'Fumiya0719', '{{masterpiece}}, {{best quality}}, {{ultra-detailed}}, {{illustration}}, 1girl, solo, light green hair, blue eyes, medium hair, embarrassed, nsfw, ', 'public hair,{{extra fingers}},extra arms,extra legs,{{troubled eyebrows}},cheek,worst quality,raised_eyebrows, error, missing fingers, extra digit, fewer digits, cropped, worst quality, {blurry}, {{mutated hands and fingers}}, mutation duplicate, extra arms, extra ears, extra eyes, extra legs, extra thighs, fused digit, fused face, fused feet, {fused fingers}, {fused hairs}, {fused hands},fused mouth, {fused thigh gap},{long body}, {missing arm}, missing digit, missing ears, missing thigh gap, missing thighs, {one hand with less than 5 digit}, {one hand with less than 5 fingers}, {one hand with more than 5 digit}, {one hand with more than 5 fingers}, {poorly drawn}, thick lips, unnatural body', '緑髪碧眼テンプレート(R18)', 'Z', NULL, '', 'Portrait (Normal) 512x768', '4052918217, 4052918218, 719', '2022-10-12 14:29:14', '2022-11-06 03:55:13'),
(6, 'FUMiNML', '{{masterpiece}}, best quality, solo, 1girl, light smile, [blush], {medium breasts}, {{{younger}}}, {light blue eyes}, medium hair, [[[side braid]]], {green hair}, ', '{{extra fingers}}, poorly drawn hands, poorly drawn face, {mutation},{deformed}, bad anatomy ,bad proportions,{{extra limbs}},heterochromia', 'test', 'A', NULL, '0', 'Portrait (Normal) 512x768', 'test', '2022-10-12 17:51:10', NULL),
(24, 'Fumiya0719', '{{masterpiece}}, best quality, solo, 1girl, light smile, [blush], {medium breasts}, {{{younger}}}, {blue eyes}, medium hair, [[[side braid]]], wind, sunrise, school uniform, {{light green hair}}, {arms behind back}, floating islands', '{{extra fingers}}, poorly drawn hands, poorly drawn face, {mutation},{deformed}, bad anatomy ,bad proportions,{{extra limbs}},heterochromia', 'エモ、海の向こうに山、日の出、セーラー服、風', 'A', 'img6366b4e84d9cb.png', '981117330', 'Portrait (Normal) 512x768', '', '2022-10-14 10:50:10', '2022-11-06 04:09:28'),
(25, 'Fumiya0719', '{{masterpiece}}, best quality, solo, 1girl, [blush], {medium breasts}, {younger}, {blue eyes}, medium hair, [[[side braid]]], daytime, {splashing}, wind, {school uniform}, ocean, {light green hair}, {light smile}, ', '{{extra fingers}}, poorly drawn hands, poorly drawn face, {mutation},{deformed}, bad anatomy ,bad proportions,{{extra limbs}},heterochromia', '海、水しぶき、セーラー服、日中、太陽光', 'A', 'img637b6d00741bd.png', '2417073760', 'Portrait (Normal) 512x768', '', '2022-10-14 11:16:01', '2022-11-21 21:20:16'),
(27, 'Fumiya0719', '{{masterpiece}}, best quality, solo, 1girl, {{light smile}}, {medium breasts}, {blue eyes}, {{medium hair}}, [[[side braid]]], {{{light green hair}}}, {{loli}}, daytime, {embarrassed}, nsfw, frilled swimsuit, swimsuit aside, {wet sweat}, covered nipples, poolside, ', '{{extra fingers}}, poorly drawn hands, poorly drawn face, {mutation},{deformed}, bad anatomy ,bad proportions,{{extra limbs}},heterochromia', 'プールサイド、汗、濡れ、透け、フリルビキニ', 'Z', 'img6366b524a91e9.png', '1617117909', 'Portrait (Normal) 512x768', '3560083398, 3560083399', '2022-10-16 08:11:13', '2022-11-06 04:10:28'),
(28, 'Fumiya0719', '{{masterpiece}}, best quality, solo, 1girl, {{light smile}}, {blue eyes}, {{medium hair}}, [[[side braid]]], {{{light green hair}}}, {{{loli}}}, ocean, wind, daytime, [[large breasts]], {{{frilled bikini}}}, {{blush}}, ', '{{extra fingers}}, poorly drawn hands, poorly drawn face, {mutation},{deformed}, bad anatomy ,bad proportions,{{extra limbs}},heterochromia', '海、日中、ビキニ、エモ', 'A', 'img6366b69d11eb1.png', '3114951137', 'Portrait (Normal) 512x768', '解像度large-portrait, 以下の反呪文でも可↓\r\npublic hair,{{extra fingers}},extra arms,extra legs,{{troubled eyebrows}},cheek,worst quality,raised_eyebrows, error, missing fingers, extra digit, fewer digits, cropped, worst quality, {blurry}, {{mutated hands and fingers}}, mutation duplicate, extra arms, extra ears, extra eyes, extra legs, extra thighs, fused digit, fused face, fused feet, {fused fingers}, {fused hairs}, {fused hands},fused mouth, {fused thigh gap},{long body}, {missing arm}, missing digit, missing ears, missing thigh gap, missing thighs, {one hand with less than 5 digit}, {one hand with less than 5 fingers}, {one hand with more than 5 digit}, {one hand with more than 5 fingers}, {poorly drawn}, thick lips, unnatural body', '2022-10-16 08:18:29', '2022-11-06 04:16:45'),
(29, 'Fumiya0719', '{{masterpiece}}, {{{best quality}}}, {ultra-detailed}, {illustration}, {1girl}, {solo}, wind, {{younger}}, {{river}}, {{school uniform}}, [[[[wet clothes]]]], {daytime}, light green hair, blue eyes, {{:d}}, light smile, {{{{water_splash}}}}, an arm in front of head', '{{extra fingers}}, poorly drawn hands, poorly drawn face, {mutation},{deformed}, bad anatomy ,bad proportions,{{extra limbs}},heterochromia', '川、水しぶき、森林、セーラー服、微濡れ透け', 'A', 'img6366b69001258.png', '2552385774', 'Portrait (Normal) 512x768', '118091647, step24~28, scale11~12', '2022-10-19 20:05:17', '2022-11-06 04:16:32'),
(30, 'Fumiya0719', '{{{best quality}}}, {{masterpiece}}, {{illustration}}, {{1girl}}, {{solo}}, {cowboy shot}, {medium breasts}, medium hair, light green hair, blue eyes, {blush}, {wind}, {{night}}, {wedding dress}, {younger}, evil smile, {beach}, {starry sky}, depth of field,', '{{extra fingers}}, poorly drawn hands, poorly drawn face, {mutation},{deformed}, bad anatomy ,bad proportions,{{extra limbs}},heterochromia,2girl,multiple girls,distortion,inn,lodging,house,lamp,street lamp,dress', '海岸、夜、星空、ウエディングドレス', 'A', 'img637b6d0dd6085.png', '3749749072', 'LandScape (Large) 1024x512', '960x512, step24 scale13', '2022-10-20 00:15:50', '2022-11-21 21:20:30'),
(31, 'Fumiya0719', '{{masterpiece}}, {{{best quality}}}, {{illustration}}, {{ultra-detailed}}, solo, 1girl, {blush}, cowboy shot, light smile, {wind}, {blonde hair}, {{aqua eyes}}, {beach}, [[younger]], {{:o}}, {{{medium breasts}}}, depth of field, starry nebula, {{white one-piece dress}}', '{{extra fingers}}, poorly drawn hands, poorly drawn face, {mutation},{deformed}, bad anatomy ,bad proportions,{{extra limbs}},heterochromiapublic hair,{{extra fingers}},extra arms,extra legs,{{troubled eyebrows}},cheek,worst quality,raised_eyebrows, error, missing fingers, cropped, worst quality, {{mutated hands and fingers}}, extra thighs, fused face, fused feet, {fused fingers}, {fused hairs}, {fused hands},fused mouth,{one hand with less than 5 fingers}, {one hand with more than 5 fingers},bad anatom,octane render,Photorealistic,hyperrealistic,realistic,Realism,building,star,particle,different eyes', '金髪、碧眼、星空、風、ビーチ、白ワンピ', 'A', 'img6366b542a1d23.png', '106412430', 'LandScape (Large) 1024x512', 'step32 scale11\r\nenhance+5でいい感じ', '2022-10-21 18:32:40', '2022-11-06 04:10:58'),
(32, 'Fumiya0719', '{illustration}, {{ultra-detailed}}, {{masterpiece}}, {{{best quality}}}, {1girl}, {solo}, [blush], {{{younger}}}, [blue eyes], {medium breasts}, wind, medium hair, {light green hair}, [night], [light smile], {{school uniform}}, looking back, rooftop, depth of field, [[starry sky]], {{glass bridge}}, detailed cloud, {{above the clouds}}', 'missing fingers, {{mutated hands and fingers}}, tree, leaves, suburb, ruin, word, nametag, indoor, forest, building, house', '橋、雲の上、横顔、風、星空', 'A', 'img637b6d33d4e84.png', '288254786', 'LandScape (Normal) 768x512', '3046142879もアリ(但し橋の造りが微妙)', '2022-10-22 00:26:24', '2022-11-22 15:36:24'),
(34, 'Fumiya0719', '{{masterpiece}}, best quality, solo, 1girl, {{{younger}}}, {blue eyes}, medium hair, {light green hair}, {{sports bra}}, cowboy shot, {{medium breasts}}, {{track}}, wiping sweat, daytime, {{{towel}}}, {sweating}, [[[[steam]]]], ', '{{extra fingers}}, poorly drawn hands, poorly drawn face, {mutation},{deformed}, bad anatomy ,bad proportions,{{extra limbs}},heterochromia,steam', 'スポブラ、汗を拭く、疲れ、トラック、日中', 'C', 'img637dfee5aff2a.png', '773261233', 'Portrait (Normal) 512x768', 'スポブラはwhite sports braの方が良いの出る', '2022-10-24 00:41:57', '2022-11-23 20:07:17'),
(35, 'Fumiya0719', '{masterpiece}, {{best quality}}, {ultra-detailed}, {illustration}, solo, 1girl, depth of field, green hair, blue eyes, cherry blossom, {{younger}}, school uniform, blush, medium hair, daytime, cowboy shot, [[wind]], :o, park, {surrounded_by_sakura}', 'lowres, bad anatomy, bad hands, text, error, missing fingers, extra digit, fewer digits, cropped, worst quality, low quality, normal quality, jpeg artifacts, signature, watermark, username, blurry, {{extra fingers}}, poorly drawn hands, poorly drawn face, {mutation},{deformed}, bad anatomy ,bad proportions,{{extra limbs}},heterochromia,row of trees,', '桜、振り向き、エモ、公園、風、セーラー服', 'A', 'img637b6d3a71061.png', '3938944927', 'Portrait (Normal) 512x768', 'Add quality tagsはon', '2022-10-27 22:36:00', '2022-11-21 21:21:14'),
(36, 'Fumiya0719', '{{masterpiece}}, best quality, nsfw, solo, 1girl, light green hair, blue eyes, medium hair, {{medium breasts}}, {{loli}}, [[steam]], [[wet sweat]], stomach, from above, [[see-through]], light smile, [[embarrassed]], {{gym storeroom}}, {{white sports bra}}', '{{extra fingers}}, poorly drawn hands, poorly drawn face, {mutation},{deformed}, bad anatomy ,bad proportions,{{extra limbs}},heterochromia,nippless,covered nippless,small breasts', '屋内、スポーツブラ、微透け、蒸気', 'Z', 'img6366b3350e480.png', '1389255778', 'Portrait (Normal) 512x768', '', '2022-10-28 00:25:04', '2022-11-06 04:02:13'),
(37, 'Fumiya0719', '{{masterpiece}}, {{best quality}}, {{ultra-detailed}}, {{illustration}}, solo, 1girl, {nsfw}, depth of field, light smile, {{medium breasts}}, {{{{loli}}}}, medium hair, {{nude}}, {forest}, nipples, embarrassed, sweating, blue eyes, light green hair, {pov}, {walking}', '{{extra fingers}}, poorly drawn hands, poorly drawn face, {mutation},{deformed}, bad anatomy ,bad proportions,{{extra limbs}},heterochromia,nippless,covered nippless,small breasts,flat chests,road,concrete,hold in hand,rope,clothes,cloak,cane,tears', '森林、裸', 'Z', 'img6366b1f47f98c.png', '931459591', 'Portrait (Normal) 512x768', '', '2022-10-28 01:18:38', '2022-11-06 03:56:52'),
(38, 'Fumiya0719', '{{masterpiece}}, {{best quality}}, {{ultra-detailed}}, {{{illustration}}}, {nsfw}, solo, 1girl, medium hair, blonde hair, aqua eyes, {{{loli}}}, large breasts, beach, {:o}, embarrassed, daytime, {wet sweat}, see-through, [[micro bikini]], {white bikini skirt}, arms behind hip', ',public hair,{{extra fingers}},extra arms,extra legs,{{troubled eyebrows}},cheek,worst quality,raised_eyebrows, error, missing fingers, extra digit, fewer digits, cropped, worst quality, {blurry}, {{mutated hands and fingers}}, mutation duplicate, extra arms, extra ears, extra eyes, extra legs, extra thighs, fused digit, fused face, fused feet, {fused fingers}, {fused hairs}, {fused hands},fused mouth, {fused thigh gap},{long body}, {missing arm}, missing digit, missing ears, missing thigh gap, missing thighs, {one hand with less than 5 digit}, {one hand with less than 5 fingers}, {one hand with more than 5 digit}, {one hand with more than 5 fingers}, {poorly drawn}, thick lips, unnatural body,seat,parasol,mat,{{nipples}},covered nipples', '金髪、碧眼、白スカート・マイクロビキニ、ビーチ、日中、R-15', 'Z', 'img6366b27a82ca3.png', '2400082283', 'Portrait (Normal) 512x768', 'nsfw+2でビキニからはみ出る乳輪、(+3だと崩壊するのでBANのnipples関連を消す)', '2022-10-28 02:10:17', '2022-11-06 03:59:06'),
(40, 'Fumiya0719', '{{masterpiece}}, {{best quality}}, {{ultra-detailed}}, {{{illustration}}}, {{nsfw}}, solo, 1girl, medium hair, blonde hair, aqua eyes, {{{loli}}}, {large breasts}, beach, {:o}, {{embarrassed}}, daytime, nude, {wet sweat}, {shiny skin}, steam, {{nipples}}, cleft of venus, puffy nipple, arms behind hip, breath', '{{extra fingers}}, poorly drawn hands, poorly drawn face, {mutation},{deformed}, bad anatomy ,bad proportions,{{extra limbs}},heterochromia', '金髪、碧眼、全裸、ビーチ、日中、吐息、R-18', 'Z', 'img6366b24bd9a72.png', '2400082283', 'Portrait (Normal) 512x768', '↑から服を消した差分的なやつ', '2022-10-28 02:38:35', '2022-11-06 03:58:20'),
(41, 'Fumiya0719', '{{masterpiece}}, {{best quality}}, {{ultra-detailed}}, {{{illustration}}}, {{nsfw}}, solo, 1girl, medium hair, blonde hair, aqua eyes, {{{loli}}}, {large breasts}, beach, {:o}, {{embarrassed}}, daytime, nude, {wet sweat}, {shiny skin}, steam, {{nipples}}, cleft of venus, puffy nipple, white liquid, arms behind hip, breath, pubic hair', ',public hair,{{extra fingers}},extra arms,extra legs,{{troubled eyebrows}},cheek,worst quality,raised_eyebrows, error, missing fingers, extra digit, fewer digits, cropped, worst quality, {blurry}, {{mutated hands and fingers}}, mutation duplicate, extra arms, extra ears, extra eyes, extra legs, extra thighs, fused digit, fused face, fused feet, {fused fingers}, {fused hairs}, {fused hands},fused mouth, {fused thigh gap},{long body}, {missing arm}, missing digit, missing ears, missing thigh gap, missing thighs, {one hand with less than 5 digit}, {one hand with less than 5 fingers}, {one hand with more than 5 digit}, {one hand with more than 5 fingers}, {poorly drawn}, thick lips, unnatural body,seat,parasol,mat,{{nipples}},covored nipples', '金髪、碧眼、全裸、ビーチ、日中、吐息、R-18、陰毛', 'Z', 'img6366b1dae08ee.png', '2400082283', 'Portrait (Normal) 512x768', '↑の陰毛差分、何故か乳首系BANを入れておいた方が差分ぽくなる\r\n追記：puffy nipple&amp;quot;s&amp;quot;だったが、訂正したらBANと相殺されて崩れるのでそのまま', '2022-10-28 02:44:03', '2022-11-06 03:56:27'),
(45, 'Fumiya0719', '{{masterpiece}}, {{{best quality}}}, {{ultra-detailed}}, {{illustration}}, 1girl, solo, cowboy shot, light green hair, blue eyes, medium hair, {{loli}}, [large breasts], nsfw, {{gym uniform}}, {{highleg}}, wet sweat, :o, [[steam]], blush, locker room', ',public hair,{{extra fingers}},extra arms,extra legs,{{troubled eyebrows}},cheek,worst quality,raised_eyebrows, error, missing fingers, extra digit, fewer digits, cropped, worst quality, {blurry}, {{mutated hands and fingers}}, mutation duplicate, extra arms, extra ears, extra eyes, extra legs, extra thighs, fused digit, fused face, fused feet, {fused fingers}, {fused hairs}, {fused hands},fused mouth, {fused thigh gap},{long body}, {missing arm}, missing digit, missing ears, missing thigh gap, missing thighs, {one hand with less than 5 digit}, {one hand with less than 5 fingers}, {one hand with more than 5 digit}, {one hand with more than 5 fingers}, {poorly drawn}, thick lips, unnatural body,{nipples},covered nipples,name tag,elder', 'ロッカールーム、体操服、巨乳、汗・蒸気(微)', 'Z', 'img636559c322155.png', '3409100827', 'Portrait (Normal) 512x768', '3762283857', '2022-10-30 15:01:22', '2022-11-05 03:28:19'),
(46, 'Fumiya0719', '{{masterpiece}}, {{{best quality}}}, {{ultra-detailed}}, {{illustration}}, beach, 1girl, solo, {cowboy shot}, light green hair, medium hair, blue eyes, nsfw, {{{loli}}}, {{school swimsuit}}, large breasts, :o, blush, ', ',public hair,{{extra fingers}},extra arms,extra legs,{{troubled eyebrows}},cheek,worst quality,raised_eyebrows, error, missing fingers, extra digit, fewer digits, cropped, worst quality, {blurry}, {{mutated hands and fingers}}, mutation duplicate, extra arms, extra ears, extra eyes, extra legs, extra thighs, fused digit, fused face, fused feet, {fused fingers}, {fused hairs}, {fused hands},fused mouth, {fused thigh gap},{long body}, {missing arm}, missing digit, missing ears, missing thigh gap, missing thighs, {one hand with less than 5 digit}, {one hand with less than 5 fingers}, {one hand with more than 5 digit}, {one hand with more than 5 fingers}, {poorly drawn}, thick lips, unnatural body,{{nipples}},covored nipples,name tag,2girl,tag', '海岸、スク水、ロリ、巨乳', 'Z', 'img636559eb864f9.png', '884318460', 'Portrait (Normal) 512x768', '', '2022-10-30 17:52:07', '2022-11-05 03:28:59'),
(47, 'Fumiya0719', '{{masterpiece}}, {{{best quality}}}, {{ultra-detailed}}, {{illustration}}, 1girl, solo, cowboy shot, rain, {{outdoors}}, depth of field, [red hair], [red eyes], long hair, blush, :o, {{loli}}, {{{large breasts}}}, {{school uniform}}, [[wet clothes]], nsfw, in main street, ', ',public hair,{{extra fingers}},extra arms,extra legs,{{troubled eyebrows}},cheek,worst quality,raised_eyebrows, error, missing fingers, extra digit, fewer digits, cropped, worst quality, {blurry}, {{mutated hands and fingers}}, mutation duplicate, extra arms, extra ears, extra eyes, extra legs, extra thighs, fused digit, fused face, fused feet, {fused fingers}, {fused hairs}, {fused hands},fused mouth, {fused thigh gap},{long body}, {missing arm}, missing digit, missing ears, missing thigh gap, missing thighs, {one hand with less than 5 digit}, {one hand with less than 5 fingers}, {one hand with more than 5 digit}, {one hand with more than 5 fingers}, {poorly drawn}, thick lips, unnatural body,{{nipples}},covored nipples,flat chest,', '雨宿り、雨、赤髪ロング、セーラー服、微透け、バス停(?)', 'Z', 'img6365597837166.png', '4262615302', 'Portrait (Normal) 512x768', '', '2022-10-30 21:02:41', '2022-11-05 03:27:04'),
(64, 'Fumiya0719', '{{masterpiece}}, {{{best quality}}}, {{ultra-detailed}}, {{illustration}}, 1girl, solo, {shower}, cowboy shot, light green hair, {{{loli}}}, {{{{little girl}}}}, {{{white bikini}}}, medium hair, blue eyes, :o, large breasts, nsfw, ', 'public hair,{{extra fingers}},extra arms,extra legs,{{troubled eyebrows}},cheek,worst quality,raised_eyebrows, error, missing fingers, extra digit, fewer digits, cropped, worst quality, {blurry}, {{mutated hands and fingers}}, mutation duplicate, extra arms, extra ears, extra eyes, extra legs, extra thighs, fused digit, fused face, fused feet, {fused fingers}, {fused hairs}, {fused hands},fused mouth, {fused thigh gap},{long body}, {missing arm}, missing digit, missing ears, missing thigh gap, missing thighs, {one hand with less than 5 digit}, {one hand with less than 5 fingers}, {one hand with more than 5 digit}, {one hand with more than 5 fingers}, {poorly drawn}, thick lips, unnatural body,{nipples},covered nipples,elder,flat chest,small breasts', 'シャワー室、白ビキニ', 'C', 'img637dfed36894b.png', '3275892115', 'Portrait (Normal) 512x768', '', '2022-11-01 21:32:55', '2022-11-23 20:06:59'),
(65, 'Fumiya0719', '{{masterpiece}}, {{{best quality}}}, {{ultra-detailed}}, {{illustration}}, 1girl, solo, {shower}, cowboy shot, light green hair, {{{loli}}}, {{{little girl}}}, {{{white bikini}}}, medium hair, blue eyes, :o, large breasts, nsfw, ', 'public hair,{{extra fingers}},extra arms,extra legs,{{troubled eyebrows}},cheek,worst quality,raised_eyebrows, error, missing fingers, extra digit, fewer digits, cropped, worst quality, {blurry}, {{mutated hands and fingers}}, mutation duplicate, extra arms, extra ears, extra eyes, extra legs, extra thighs, fused digit, fused face, fused feet, {fused fingers}, {fused hairs}, {fused hands},fused mouth, {fused thigh gap},{long body}, {missing arm}, missing digit, missing ears, missing thigh gap, missing thighs, {one hand with less than 5 digit}, {one hand with less than 5 fingers}, {one hand with more than 5 digit}, {one hand with more than 5 fingers}, {poorly drawn}, thick lips, unnatural body,{nipples},covered nipples,elder,flat chest,small breasts', 'シャワー室、白ビキニ(その２)', 'Z', 'img63655a54b24f0.png', '94885520', 'Portrait (Normal) 512x768', '', '2022-11-01 21:33:52', '2022-11-05 03:30:44'),
(66, 'Fumiya0719', '{{masterpiece}}, {{{best quality}}}, {{ultra-detailed}}, {{illustration}}, 1girl, solo, light green hair, blue eyes, {{{{{{loli}}}}}}, {{little girl}}, {{{{{huge breasts}}}}}, medium hair, :o, {{{{nsfw}}}}, {{wet sweat}}, {{{heavy breathing}}}, {{{{{pussy juice}}}}}, {{{{{{cum on body}}}}}}, {{{{shiny skin}}}}, {{steam}}, {{{{embarrassed}}}}, pussy, {{{{puffy nipples}}}}, {{{{{{{{{breast milk}}}}}}}}}, {{{{{{serafuku}}}}}}, {{classroom}}, {{squatting}}, {{arms behind head}}, nude, hand milking, drip milk,', 'public hair,{{extra fingers}},extra arms,extra legs,{{troubled eyebrows}},cheek,worst quality,raised_eyebrows, error, missing fingers, extra digit, fewer digits, cropped, worst quality, {blurry}, {{mutated hands and fingers}}, mutation duplicate, extra arms, extra ears, extra eyes, extra legs, extra thighs, fused digit, fused face, fused feet, {fused fingers}, {fused hairs}, {fused hands},fused mouth, {fused thigh gap},{long body}, {missing arm}, missing digit, missing ears, missing thigh gap, missing thighs, {one hand with less than 5 digit}, {one hand with less than 5 fingers}, {one hand with more than 5 digit}, {one hand with more than 5 fingers}, {poorly drawn}, thick lips, unnatural body,elder,flat chest,small breasts,{{paizuri}}', 'M字開脚、手縛り、母乳(?)、爆乳、愛液、R-18', 'Z', 'img63655b91b4464.png', '3275892115', 'Portrait (Normal) 512x768', '性  癖  大  合  掌', '2022-11-01 22:45:45', '2022-11-05 03:36:01'),
(67, 'Fumiya0719', '{{masterpiece}}, {{best quality}}, {{ultra-detailed}}, {{{illustration}}}, {{nsfw}}, solo, 1girl, medium hair, blonde hair, aqua eyes, {{{{outdoors}}}}, {{{{beach}}}}, {:o}, {{embarrassed}}, daytime, {{{{wet sweat}}}}, {{{{{{loli}}}}}}, {{{{{{little girl}}}}}}, {shiny skin}, steam, {{nipples}}, {{{{huge breasts}}}}, {{{{squatting}}}}, {{{{{{{{cum on body}}}}}}}}, {{heavy breathing}}, {{{{serafuku}}}}, ', ',public hair,{{extra fingers}},extra arms,extra legs,{{troubled eyebrows}},cheek,worst quality,raised_eyebrows, error, missing fingers, extra digit, fewer digits, cropped, worst quality, {blurry}, {{mutated hands and fingers}}, mutation duplicate, extra arms, extra ears, extra eyes, extra legs, extra thighs, fused digit, fused face, fused feet, {fused fingers}, {fused hairs}, {fused hands},fused mouth, {fused thigh gap},{long body}, {missing arm}, missing digit, missing ears, missing thigh gap, missing thighs, {one hand with less than 5 digit}, {one hand with less than 5 fingers}, {one hand with more than 5 digit}, {one hand with more than 5 fingers}, {poorly drawn}, thick lips, unnatural body,seat,parasol,mat,{{wall}},poll,pool,chair', '海岸、金髪碧眼、巨乳、セーラー服(たくし上げ)、精子、トイレ', 'Z', 'img63655ac37fe88.png', '2400082283', 'Portrait (Normal) 512x768', '精子を愛液に変えるのもまたをかし', '2022-11-02 00:46:03', '2022-11-20 03:11:07'),
(68, 'Fumiya0719', '{{masterpiece}}, {{best quality}}, {{ultra-detailed}}, {{{illustration}}}, {{{{nsfw}}}}, solo, 1girl, medium hair, blonde hair, aqua eyes, {{{{{{outdoors}}}}}}, {:o}, {{embarrassed}}, daytime, {{{{wet sweat}}}}, {{{{{{{{loli}}}}}}}}, {{{{{{{{little girl}}}}}}}}, {{{shiny skin}}}, {steam}, {{{{squatting}}}}, {{heavy breathing}}, {{{{{{{{pussy juice}}}}}}}}, {{{huge breasts}}}, straight hair, {{arms behind back}}, {{{puffy nipples}}}, {{cum on breasts}}, {{cleavage}}, {{{{{{stadium}}}}}}, {{nude}}, ', ',public hair,{{extra fingers}},extra arms,extra legs,{{troubled eyebrows}},cheek,worst quality,raised_eyebrows, error, missing fingers, extra digit, fewer digits, cropped, worst quality, {blurry}, {{mutated hands and fingers}}, mutation duplicate, extra arms, extra ears, extra eyes, extra legs, extra thighs, fused digit, fused face, fused feet, {fused fingers}, {fused hairs}, {fused hands},fused mouth, {fused thigh gap},{long body}, {missing arm}, missing digit, missing ears, missing thigh gap, missing thighs, {one hand with less than 5 digit}, {one hand with less than 5 fingers}, {one hand with more than 5 digit}, {one hand with more than 5 fingers}, {poorly drawn}, thick lips, unnatural body,seat,parasol,mat,{{wall}},poll,pool,chair,flat chest,small breasts,medium breasts', '金髪碧眼、巨乳、精子、トイレ、スタジアム', 'Z', 'img63655b2399d23.png', '2400082283', 'Portrait (Normal) 512x768', '', '2022-11-02 01:09:36', '2022-11-05 03:34:11'),
(69, 'Fumiya0719', '{{masterpiece}}, {{best quality}}, {{ultra-detailed}}, {{illustration}}, nsfw, 1girl, solo, {{outdoors}}, {{beach}}, cowboy shot, sunlight, orange hair, medium hair, side ponytail, aqua eyes, :o, blush, medium breasts, loli, {{{{white frilled swimsuit}}}}, wet clothes, ', 'public hair,{{extra fingers}},extra arms,extra legs,{{troubled eyebrows}},cheek,worst quality,raised_eyebrows, error, missing fingers, extra digit, fewer digits, cropped, worst quality, {blurry}, {{mutated hands and fingers}}, mutation duplicate, extra arms, extra ears, extra eyes, extra legs, extra thighs, fused digit, fused face, fused feet, {fused fingers}, {fused hairs}, {fused hands},fused mouth, {fused thigh gap},{long body}, {missing arm}, missing digit, missing ears, missing thigh gap, missing thighs, {one hand with less than 5 digit}, {one hand with less than 5 fingers}, {one hand with more than 5 digit}, {one hand with more than 5 fingers}, {poorly drawn}, thick lips, unnatural body,{{nipples}},covered nipples', '橙髪碧眼、白フリル水着、海岸', 'Z', 'img6366ce9aac645.png', '1460846139', 'Portrait (Normal) 512x768', '水着をwhite frilled bikini〇、背景をpool〇(ただし違和感あり)', '2022-11-06 04:57:26', '2022-11-06 05:59:06'),
(70, 'Fumiya0719', '{{masterpiece}}, {{best quality}}, {{ultra-detailed}}, {{illustration}}, 1girl, solo, {{outdoors}}, {{beach}}, cowboy shot, sunlight, orange hair, medium hair, side ponytail, aqua eyes, {{{{{nsfw}}}}}, :o, {{embarrassed}}, medium breasts, loli, {{{{white frilled swimsuit}}}}, {{{{highleg}}}}, {{{{{{wet clothes}}}}}}, {{{{{{see-through}}}}}}, {{{steam}}}, {{{heavy breathing}}}, {{{{covered nipples}}}}, {{{{pussy juice stain}}}}, {{{{{{{{cum on body}}}}}}}}, ', 'public hair,{{extra fingers}},extra arms,extra legs,{{troubled eyebrows}},cheek,worst quality,raised_eyebrows, error, missing fingers, extra digit, fewer digits, cropped, worst quality, {blurry}, {{mutated hands and fingers}}, mutation duplicate, extra arms, extra ears, extra eyes, extra legs, extra thighs, fused digit, fused face, fused feet, {fused fingers}, {fused hairs}, {fused hands},fused mouth, {fused thigh gap},{long body}, {missing arm}, missing digit, missing ears, missing thigh gap, missing thighs, {one hand with less than 5 digit}, {one hand with less than 5 fingers}, {one hand with more than 5 digit}, {one hand with more than 5 fingers}, {poorly drawn}, thick lips, unnatural body,tree', '橙髪碧眼、白フリル水着、海岸、濡れ透け、絶頂、', 'Z', 'img6366ceb36163f.png', '1460846139', 'Portrait (Normal) 512x768', '+closed eyesで片目閉じ', '2022-11-06 05:27:38', '2022-11-06 06:56:29'),
(71, 'Fumiya0719', '{{masterpiece}}, {{best quality}}, {{ultra-detailed}}, {{illustration}}, 1girl, solo, {{outdoors}}, {{beach}}, cowboy shot, sunlight, orange hair, medium hair, side ponytail, aqua eyes, {{{{{nsfw}}}}}, :o, {{embarrassed}}, loli, {{{{highleg}}}}, {{{{{{white frilled swimsuit}}}}}}, {{{{{{wet clothes}}}}}}, {{see-through}}, {{{steam}}}, {{{heavy breathing}}}, covered nipples, {{{{pussy juice stain}}}}, {{{{{{{{cum on body}}}}}}}}, {{{{{{large breasts}}}}}}, {{cleavage}}, ', 'public hair,{{extra fingers}},extra arms,extra legs,{{troubled eyebrows}},cheek,worst quality,raised_eyebrows, error, missing fingers, extra digit, fewer digits, cropped, worst quality, {blurry}, {{mutated hands and fingers}}, mutation duplicate, extra arms, extra ears, extra eyes, extra legs, extra thighs, fused digit, fused face, fused feet, {fused fingers}, {fused hairs}, {fused hands},fused mouth, {fused thigh gap},{long body}, {missing arm}, missing digit, missing ears, missing thigh gap, missing thighs, {one hand with less than 5 digit}, {one hand with less than 5 fingers}, {one hand with more than 5 digit}, {one hand with more than 5 fingers}, {poorly drawn}, thick lips, unnatural body,tree,flat chest,small breasts,', '橙髪碧眼、白フリル水着、海岸、濡れ透け、ぶっかけ(巨乳差分)', 'Z', 'img6366cec872c15.png', '1460846139', 'Portrait (Normal) 512x768', 'scale4.5~6辺り〇', '2022-11-06 05:49:06', '2022-11-06 06:05:29'),
(72, 'Fumiya0719', '{{masterpiece}}, {{best quality}}, {{ultra-detailed}}, {{illustration}}, 1girl, solo, {{outdoors}}, {{ocean}}, {{starry sky}}, {{depth of field}}, {{cowboy shot}}, light green hair, blue eyes, medium hair, :o, blush, nsfw, {{large breasts}}, {{{{loli}}}}, {{white bikini skirt}}, ', '{{extra fingers}}, poorly drawn hands, poorly drawn face, {mutation},{deformed}, bad anatomy ,bad proportions,{{extra limbs}},heterochromia{{extra fingers}}, poorly drawn hands, poorly drawn face, {mutation},{deformed}, bad anatomy ,bad proportions,{{extra limbs}},heterochromia,flat chest,small breasts,{{tree}},nipples,covered nipples', '海岸、星空、白ビキニスカート、事前', 'Z', NULL, '654741172', 'Portrait (Normal) 512x768', '', '2022-11-06 08:09:55', '2022-11-06 08:09:55'),
(73, 'Fumiya0719', '{{masterpiece}}, {{best quality}}, {{ultra-detailed}}, {{illustration}}, 1girl, solo, cowboy shot, sunlight, orange hair, medium hair, {{indoors}}, {{{{classroom}}}}, side ponytail, aqua eyes, [[:o]], {{embarrassed}}, {{{loli}}}, {{{little girl}}}, {{{steam}}}, {{{{{nsfw}}}}}, {{heavy breathing}}, {{{{{{{{pussy juice}}}}}}}}, {{{{wet sweat}}}}, {{{{squatting}}}}, {{nude}}, {{{{puffy nipples}}}}, {{cleavage}}, arms behind back, {{huge breasts}}, {{red collar}}, {{{{{{restrained}}}}}}', 'public hair,{{extra fingers}},extra arms,extra legs,{{troubled eyebrows}},cheek,worst quality,raised_eyebrows, error, missing fingers, extra digit, fewer digits, cropped, worst quality, {blurry}, {{mutated hands and fingers}}, mutation duplicate, extra arms, extra ears, extra eyes, extra legs, extra thighs, fused digit, fused face, fused feet, {fused fingers}, {fused hairs}, {fused hands},fused mouth, {fused thigh gap},{long body}, {missing arm}, missing digit, missing ears, missing thigh gap, missing thighs, {one hand with less than 5 digit}, {one hand with less than 5 fingers}, {one hand with more than 5 digit}, {one hand with more than 5 fingers}, {poorly drawn}, thick lips, unnatural body,elder,flat chest,small breasts', '橙髪碧眼、教室、拘束、M字開脚、爆乳', 'Z', 'img6367099cb576d.png', '1460846139', 'Portrait (Normal) 512x768', 'step32, scale10 huge breastsとrestrainedは強さ調節可\r\nenhance*4〇', '2022-11-06 09:29:12', '2022-11-06 10:10:52'),
(74, 'Fumiya0719', '{{masterpiece}}, {{best quality}}, {{ultra-detailed}}, {{illustration}}, 1girl, solo, cowboy shot, sunlight, orange hair, medium hair, {{indoors}}, {{{{classroom}}}}, side ponytail, aqua eyes, :o, {blush}, {{loli}}, {{little girl}}, [[[[huge breasts]]]], arms behind back, {{school uniform}}, nsfw, ', 'public hair,{{extra fingers}},extra arms,extra legs,{{troubled eyebrows}},cheek,worst quality,raised_eyebrows, error, missing fingers, extra digit, fewer digits, cropped, worst quality, {blurry}, {{mutated hands and fingers}}, mutation duplicate, extra arms, extra ears, extra eyes, extra legs, extra thighs, fused digit, fused face, fused feet, {fused fingers}, {fused hairs}, {fused hands},fused mouth, {fused thigh gap},{long body}, {missing arm}, missing digit, missing ears, missing thigh gap, missing thighs, {one hand with less than 5 digit}, {one hand with less than 5 fingers}, {one hand with more than 5 digit}, {one hand with more than 5 fingers}, {poorly drawn}, thick lips, unnatural body,elder,flat chest,small breasts', '橙髪碧眼、教室、セーラー服、巨乳', 'Z', 'img636709a662db4.png', '1460846139', 'Portrait (Normal) 512x768', 'step32, scale10 一応前の奴の事前差分という名目だがほぼ似てない', '2022-11-06 09:36:55', '2022-11-14 21:35:39'),
(76, 'Fumiya0719', '{{masterpiece}}, {{best quality}}, {{ultra-detailed}}, 1girl, solo, {wind}, {{{depth of field}}}, {{sundawn}}, starry sky, {skyscraper}, {outdoors}, {light green hair}, blue eyes, medium hair, hairpin, {younger}, {{serafuku}}, {{blush}}, light smile, ', 'public hair,{{extra fingers}},extra arms,extra legs,{{troubled eyebrows}},cheek,worst quality,raised_eyebrows, error, missing fingers, extra digit, fewer digits, cropped, worst quality, {blurry}, {{mutated hands and fingers}}, mutation duplicate, extra arms, extra ears, extra eyes, extra legs, extra thighs, fused digit, fused face, fused feet, {fused fingers}, {fused hairs}, {fused hands},fused mouth, {fused thigh gap},{long body}, {missing arm}, missing digit, missing ears, missing thigh gap, missing thighs, {one hand with less than 5 digit}, {one hand with less than 5 fingers}, {one hand with more than 5 digit}, {one hand with more than 5 fingers}, {poorly drawn}, thick lips, unnatural body,elder,[mountain],', 'エモ、屋上、大都市、高層ビル、風、セーラー服、日の入り、星空', 'A', 'img63698223d5313.png', '2146709803', 'Portrait (Normal) 512x768', 'scale4.5', '2022-11-08 07:09:21', '2022-11-08 07:09:40'),
(77, 'ヴァーミリオン', '1girl, twintails, small breasts, loli, tan, lifted by self, {{{clothes pull }}}, nipple slip, no bra, clothing aside, stabbed, clothes lift, mouth hold, sking fang, innocent, {{{{{panties aside}}}}}, nsfw, illustration, ultra-detailed, masterpiece, best quality, spread pussy, {{{undressing}}}, cameltoe, masturbation, gym uniform, buruma, ', '', '', 'Z', NULL, '', 'Portrait (Normal) 512x768', '', '2022-11-09 16:10:34', '2022-11-09 16:11:06'),
(117, 'Fumiya0719', 'masterpiece, best quality, ultra-detailed, 1girl, solo, (wind), ((depth of field)), (sundawn), ((starry sky)), ((skyscraper)), (outdoors), (light green hair), short hair, (blue eyes), ((((younger)))), (serafuku), (blush), light smile, ', 'public hair,((extra fingers)),extra arms,extra legs,((troubled eyebrows)),cheek,worst quality,raised_eyebrows, error, missing fingers, extra digit, fewer digits, cropped, worst quality, (blurry), ((mutated hands and fingers)), mutation duplicate, extra arms, extra ears, extra eyes, extra legs, extra thighs, fused digit, fused face, fused feet, (fused fingers), (fused hairs), (fused hands),fused mouth, (fused thigh gap),(long body), (missing arm), missing digit, missing ears, missing thigh gap, missing thighs, (one hand with less than 5 digit), (one hand with less than 5 fingers), (one hand with more than 5 digit), (one hand with more than 5 fingers), (poorly drawn), thick lips, unnatural body,elder', '日の入り、夜景、星空、高台、フェンス、風、セーラー服', 'A', 'img637e353f81a70.png', '191001463', 'LandScape (Large) 1024x512', 'anythingv3, 1280x768, step24, scale9, highres fix', '2022-11-23 23:58:58', '2022-11-24 00:04:51'),
(119, 'Fumiya0719', 'test', 'test', 'test', 'A', 'img637f6d119ce81.png', '12345', 'Portrait (Normal) 512x768', '', '2022-11-24 22:09:37', '2022-11-24 22:09:37');
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
