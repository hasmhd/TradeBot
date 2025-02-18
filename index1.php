<?php
$data = [
    "markets" => [
        ["name" => "BTC/USDT", "symbol" => "btc"],
        ["name" => "ETH/USDT", "symbol" => "eth"],
        ["name" => "BNB/USDT", "symbol" => "bnb"],
        ["name" => "ADA/USDT", "symbol" => "ada"],
        ["name" => "XRP/USDT", "symbol" => "xrp"]
    ],
    "orders" => [
        ["id" => 1, "market" => "BTC/USDT", "type" => "Buy", "price" => 45000, "amount" => 0.1, "time" => "10:30 AM"],
        ["id" => 2, "market" => "ETH/USDT", "type" => "Sell", "price" => 3200, "amount" => 1, "time" => "11:00 AM"]
    ],
    "balance" => 10000
];
?>
<!DOCTYPE html>
<html lang="fa">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>داشبورد ترید</title>
    <link href="https://cdn.jsdelivr.net/gh/rastikerdar/vazir-font/dist/font-face.css" rel="stylesheet" type="text/css" />
    <style>
        body { font-family: 'Vazir', sans-serif; direction: rtl; text-align: right; display: flex; background: #f8f9fa; }
        .sidebar { width: 20%; background: #f4f4f4; padding: 15px; height: 100vh; }
        .content { width: 80%; padding: 15px; }
        .card { background: #ffffff; padding: 15px; margin: 10px 0; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); }
        table { width: 100%; border-collapse: collapse; background: #fff; border-radius: 8px; overflow: hidden; }
        th, td { border: 1px solid #ddd; padding: 10px; text-align: center; }
        th { background: #007bff; color: white; }
        .button { background: red; color: white; padding: 5px 10px; border: none; cursor: pointer; border-radius: 4px; }
        .market-item { display: flex; align-items: center; cursor: pointer; padding: 10px; background: #e9ecef; margin-bottom: 5px; border-radius: 4px; }
        .market-item:hover { background: #d6d8db; }
        .market-item img { width: 24px; height: 24px; margin-left: 10px; }
        .popup { display: none; position: fixed; top: 50%; left: 50%; transform: translate(-50%, -50%); background: white; padding: 20px; box-shadow: 0px 0px 10px rgba(0, 0, 0, 0.3); border-radius: 8px; }
        .popup h2 { margin-top: 0; }
        .popup .close-btn { background: red; color: white; padding: 5px 10px; border: none; cursor: pointer; border-radius: 4px; margin-top: 10px; }
    </style>
    <script>
        function showPopup(marketName) {
            document.getElementById('popup-title').innerText = marketName;
            document.getElementById('popup').style.display = 'block';
        }
        function closePopup() {
            document.getElementById('popup').style.display = 'none';
        }
    </script>
</head>
<body>
    <div class="sidebar">
        <h3>📊 لیست بازارها</h3>
        <div>
            <?php foreach ($data["markets"] as $market) {
                echo "<div class='market-item' onclick=\"showPopup('{$market['name']}')\">
                        <img src='images/{$market['symbol']}.png' alt='{$market['name']}'>
                        <span>{$market['name']}</span>
                      </div>";
            } ?>
        </div>
    </div>

    <div class="content">
        <h2>💰 موجودی حساب: <?php echo $data["balance"]; ?> USDT</h2>

        <h2>📋 سفارشات فعال</h2>
        <div class="card">
            <table>
                <tr>
                    <th>بازار</th>
                    <th>نوع</th>
                    <th>قیمت</th>
                    <th>مقدار</th>
                    <th>زمان</th>
                    <th>عملیات</th>
                </tr>
                <?php foreach ($data["orders"] as $order) {
                    echo "<tr>
                            <td>{$order['market']}</td>
                            <td>{$order['type']}</td>
                            <td>{$order['price']}</td>
                            <td>{$order['amount']}</td>
                            <td>{$order['time']}</td>
                            <td><button class='button' onclick='cancelOrder({$order['id']})'>❌ لغو</button></td>
                          </tr>";
                } ?>
            </table>
        </div>
    </div>

    <div id="popup" class="popup">
        <h2 id="popup-title"></h2>
        <button class="close-btn" onclick="closePopup()">بستن</button>
    </div>
</body>
</html>
