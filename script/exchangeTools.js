function sale(count, bids, commission)
{
    var totalPrice = 0.0;
    for(var i = 0; i < bids.length; i++)
    { 
        var bid = bids[i];
        if(count <= bid[1]){
            totalPrice += count * bid[0];
            break;
        }else{
            totalPrice += bid[1] * bid[0];
            count -= bid[1];
        }
    }
    var karmozd = commission * totalPrice ;
    return totalPrice - karmozd;
}
function buy(money, asks, commission)
{
    var count = 0.0;
    for(var i = 0; i < asks.length; i++)
    {
        var ask = asks[i];
        if(money > 0.0){
            var cnt = money / ask[0];
            if(cnt > ask[1]){
                count += ask[1];
                money -= ask[1] * ask[0];                        
            }else{
                count += cnt;
                money = 0.0;//-= $cnt * $ask[0];
                break;
            }
        }
    }
    var karmozd = commission * count;
    return  count - karmozd;
}
//class 