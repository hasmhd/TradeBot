<?php
function analyze_market($trades) {
    $now = microtime(true) * 1000; // زمان فعلی بر حسب میلی‌ثانیه
    $fifteenMinutesAgo = $now - (15 * 60 * 1000); // ۱۵ دقیقه قبل

    $minPrice = PHP_FLOAT_MAX;
    $maxPrice = PHP_FLOAT_MIN;
    $totalVolume = 0;
    $bidPrices = [];
    $askPrices = [];

    foreach ($trades as $trade) {
        $timestamp = $trade["time"];
        $price = $trade["price"];
        $volume = $trade["qty"];
        $isBuyerMaker = $trade["isBuyerMaker"];

        if ($timestamp >= $fifteenMinutesAgo) {
            // محاسبه نوسان قیمت
            if ($price < $minPrice) $minPrice = $price;
            if ($price > $maxPrice) $maxPrice = $price;

            // محاسبه حجم معاملات
            $totalVolume += $volume;

            // جمع‌آوری قیمت‌های خرید و فروش برای اسپرد
            if ($isBuyerMaker) {
                $bidPrices[] = $price; // خرید
            } else {
                $askPrices[] = $price; // فروش
            }
        }
    }

    // محاسبه اسپرد
    $bestBid = !empty($bidPrices) ? max($bidPrices) : 0;
    $bestAsk = !empty($askPrices) ? min($askPrices) : 0;
    $spread = ($bestAsk > 0 && $bestBid > 0) ? (($bestAsk - $bestBid) / $bestAsk) * 100 : 0;

    return [
        "volatility" => $maxPrice - $minPrice,
        "volume" => $totalVolume,
        "spread" => $spread
    ];
}

// دریافت داده‌های تمام بازارها (فرض کنیم یک آرایه داریم)
$markets = json_decode(file_get_contents("markets.json"), true);

$marketScores = [];

foreach ($markets as $marketName => $trades) {
    $analysis = analyze_market($trades);

    // امتیازدهی بازارها (هرچه نوسان و حجم بیشتر و اسپرد کمتر باشد، امتیاز بالاتر است)
    $score = ($analysis["volatility"] * $analysis["volume"]) / ($analysis["spread"] + 0.01);

    $marketScores[$marketName] = [
        "score" => $score,
        "volatility" => $analysis["volatility"],
        "volume" => $analysis["volume"],
        "spread" => $analysis["spread"]
    ];
}

// مرتب‌سازی بازارها بر اساس بهترین امتیاز
uasort($marketScores, function ($a, $b) {
    return $b["score"] <=> $a["score"];
});

// نمایش ۵ بازار برتر
echo "📊 **۵ بازار برتر برای ترید:**\n";
$topMarkets = array_slice($marketScores, 0, 5);
foreach ($topMarkets as $market => $data) {
    echo "✅ **$market** | 📈 نوسان: " . number_format($data["volatility"], 4) .
         " | 📊 حجم: " . number_format($data["volume"], 2) .
         " | 💰 اسپرد: " . number_format($data["spread"], 2) . "%\n";
}
?>
