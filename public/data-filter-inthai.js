               
    // holiday_date = Array.from([holiday_date], (x) => x );
        var menu_country = new Array();
        var menu_price = new Array();
        var menu_airline = new Array();
        var menu_rating = new Array();
        var menu_day = new Array();
        var menu_holiday = new Array();
        var menu_month = new Array();
        var menu_city = new Array();
        var menu_amupur = new Array();
        var data_tour = new Array();
        var city_slide = new Array();
        var inthai_id = inthai_id?inthai_id:0;
        var price_search = price_search?price_search:0;
        var country_search = country_search?country_search:0;
        var city_search = city_search?city_search:0;
        var keyword_search = keyword_search?keyword_search:0;
        var code_id = code_id?code_id:0;
        var start_search = start_search?start_search:0;
        var end_search = end_search?end_search:0;
        var str_start = str_start?str_start:0;
        var str_end = str_end?str_end:0;
        var travel_search = travel_search?travel_search:0;
        var tour_code = tour_code?tour_code:0;
        var isWin = isWin?isWin:0;
        var isMac = isMac?isMac:0;
        var isAndroid = isAndroid?isAndroid:0;
        var isIPhone = isIPhone?isIPhone:0;
        var isIPad = isIPad?isIPad:0;
        var count_country = 0;
        var count_city = 0;
        var count_amupur = 0;
        var count_airline = 0;
        var total_tour = 0;
        var type_data = {
            country: new Array(),
            price: new Array(),
            airline: new Array(),
            rating: new Array(),
            day:new Array(),
            month:new Array(),
            holiday:new Array(),
            city:new Array(),
            amupur:new Array(),
            start_date:new Array(),
            end_date:new Array(),
            travel_search:new Array(),
            tour_code:new Array(),
        }
        var paginat_act = 1;
        var main_tour = new Array();
        var total_page = 0;
        var days = ['วันอาทิตย์','วันจันทร์','วันอังคาร','วันพุธ','วันพฤหัสบดี','วันศุกร์','วันเสาร์'];
        var months = ['ม.ค.','ก.พ.','มี.ค.','เม.ย.','พ.ค.','มิ.ย.','ก.ค.','ส.ค.','ก.ย.','ต.ค.','พ.ย.','ธ.ค.'];
        var month_number = ['01','02','03','04','05','06','07','08','09','10','11','12'];
        var min_date = (new Date().getMonth()+1)+'/'+new Date().getDate()+'/'+new Date().getFullYear()*1;
        starter();
        $('#hide_date_select').hide();
        $('#hide_date_select_mb').hide();
        async function starter(){
            if(inthai_id){
                tour = await tour.filter(x => x.province_id.includes('"'+inthai_id+'"'));
                var tour_fill = new Array();
                for(let t in tour){
                    tour_fill.push(tour[t].id);
                }
                period = await period.filter(x=> tour_fill.includes(x.tour_id));
            }else{
                data_tour = tour;
            }
            await show_tour(0);
            // await readMore();
            await SelectFilter();
            await menu_filter();

            if(!inthai_id){
                await show_country();
                $('#city-topic').hide();
                $('#amupur-topic').hide();
            }
            if(inthai_id){
                await show_amupur(true);
                $('#country-topic').hide();
                $('#city-topic').hide();
            }
            await show_price();
            await show_airline();
            await show_rating();
            await show_day();
            await show_month();
            await show_holiday();
            await date_picker();
            await filter_tour();
            
            
         }
         async function date_picker(){
                if(start_search && end_search){
                    var check_after = new Date(start_search);
                    var check_befor = new Date(end_search);
                    var searchS_show = check_after.getDate()+'/'+month_number[check_after.getMonth()]+'/'+(check_after.getFullYear()*1+543);
                    var searchE_show = check_befor.getDate()+'/'+month_number[check_befor.getMonth()]+'/'+(check_befor.getFullYear()*1+543);
                    if(isWin || isMac){
                        document.getElementById('show_select_date').innerHTML = "<li onclick='DeletedDate()'><label class='check-container'>"+searchS_show+" ถึง "+searchE_show+"<i class='fa fa-times-circle' aria-hidden='true'></i></label></li>";
                    }else if(isIPhone || isAndroid || isIPad){   
                        //mobile
                        document.getElementById('show_select_date_mb').innerHTML = "<li onclick='DeletedDate()'><label class='check-container'>"+searchS_show+" ถึง "+searchE_show+"<i class='fa fa-times-circle' aria-hidden='true'></i></label></li>";  
                        document.getElementById('show_select_date_all').innerHTML = "<li onclick='DeletedDate()'><label class='check-container'>"+searchS_show+" ถึง "+searchE_show+"<i class='fa fa-times-circle' aria-hidden='true'></i></label></li>";  
                    }
                    type_data.start_date.push(start_search)
                    type_data.end_date.push(end_search)
                    count_pagin = 1;
                    
                }else{
                    var check_after = new Date();
                    var check_befor = new Date(check_after.valueOf()+86400000);
                }
            
                var strat_show = check_after.getDate()+'  '+months[check_after.getMonth()]+'  '+(check_after.getFullYear()*1+543);
                var start_day_show = days[check_after.getDay()];
                var end_show = check_befor.getDate()+'  '+months[check_befor.getMonth()]+'  '+(check_befor.getFullYear()*1+543);
                var end_day_show = days[check_befor.getDay()];
                var text_s_show = '';
                    text_s_show += "<span style='font-size:0.8rem;padding:3px 2px;display:block;color:gray;'>"+strat_show+"</span>";
                    text_s_show += "<span style='font-size:0.8rem;padding:3px 2px;display:block;color:gray;'>"+start_day_show+"</span>";
                var text_e_show = '';
                    text_e_show += "<span style='font-size:0.8rem;padding:3px 2px;display:block;color:gray;'>"+end_show+"</span>";
                    text_e_show += "<span style='font-size:0.8rem;padding:3px 2px;display:block;color:gray;'>"+end_day_show+"</span>";
            if(isWin || isMac){
                document.getElementById('show_date_calen').innerHTML = text_s_show;
                document.getElementById('show_end_calen').innerHTML = text_e_show;
            }else if(isIPhone || isAndroid || isIPad){
                //mobile
                document.getElementById('show_date_calen_mb').innerHTML = text_s_show;
                document.getElementById('show_end_calen_mb').innerHTML = text_e_show;
            }
           
         }
        async function count_paginate(page){
         
            var min = page-6;
            if(min <= 0){
                min = min-min;
            }
            var btn = '';
            var max = total_page < 12?total_page:12;
            if(paginat_act){          
                btn+= "<a class='pagination-newer' onclick='show_tour("+(paginat_act-1)+")'><i class='fas fa-angle-left'></i></a>";
            }
            for(x=min;x<min+max;x++){
                if((x+1)<=max){
                    btn+= "<span class='pagination-inner'>";
                    btn+=     "<a herf='javascript:void(0);'"; 
                                if(x == paginat_act){
                    btn+=           " class='pagination-active'"; 
                                }
                    btn+=   "  onclick='show_tour("+x+")' id='page"+(x+1)+"'>"+(x+1)+"</a>";
                    btn+= "</span>";
                }
            }
            if(paginat_act != total_page-1 && total_page > 1){
                btn+=  "<a class='pagination-older' onclick='show_tour("+(paginat_act+1)+")'><i class='fas fa-angle-right'></i></a>";
            }
            document.getElementById('pagination').innerHTML = btn;
        }
        async function menu_filter(){
            //เมนูประเทศ
            for(let x in country){
                var id_country = country[x].id;
                var num = await tour.filter(x => x.country_id.includes('"'+id_country+'"')).length;
                if(num){
                    let info = {
                        id:country[x].id,
                        name:country[x].country_name_th != ''?country[x].country_name_th:country[x].country_name_en,
                        num:num,
                    }
                    if(!menu_country[count_country]){
                        menu_country[count_country] = new Array();
                    }
                    menu_country[count_country].push(info);
                    if(menu_country[count_country].length >= 10 && count_country == 0){
                        count_country++;
                    }
                }
            }
            //เมนูประเทศ
            //เมนูเมือง
            for(let x in city){
                var id_city = city[x].id;
                var num = await tour.filter(x => x.city_id.includes('"'+id_city+'"')).length;
                if(num){
                    let info = {
                        id:city[x].id,
                        name:city[x].city_name_th != ''?city[x].city_name_th:city[x].city_name_en,
                        num:num,
                    }
                    if(!menu_city[count_city]){
                        menu_city[count_city] = new Array();
                    }
                    menu_city[count_city].push(info);
                    if(menu_city[count_city].length >= 10 && count_city == 0){
                        count_city++;
                    }
                }
            }
            //เมนูเมือง
            //เมนูอำเภอ
            for(let x in amupur){
                var id_amupur = amupur[x].id;
                var num = await tour.filter(x => x.district_id.includes('"'+id_amupur+'"')).length;
                if(num){
                    let info = {
                        id:amupur[x].id,
                        name:amupur[x].name_th != ''?amupur[x].name_th:amupur[x].name_en,
                        num:num,
                    }
                    if(!menu_amupur[count_amupur]){
                        menu_amupur[count_amupur] = new Array();
                    }
                    menu_amupur[count_amupur].push(info);
                    if(menu_amupur[count_amupur].length >= 10 && count_amupur == 0){
                        count_amupur++;
                    }
                    city_slide.push(info);
                }
            }
            //เมนูอำเภอ
            //เมนูราคา
            for(let p in price){
                var num = await tour.filter(x => x.price_group == p ).length;
                if(num){
                    let info = {
                        id:p,
                        name:price[p],
                        num:num,
                    }
                    menu_price.push(info);
                }
            }
            //เมนูราคา
            //เมนูสายการบิน
            for(let x in airline){
                var id_airline = airline[x].id;
                var num = await tour.filter(x => x.airline_id == id_airline).length;
                if(num){
                    let info = {
                        id:airline[x].id,
                        name:airline[x].travel_name,
                        num:num,
                        img:airline[x].image,
                    }
                    if(!menu_airline[count_airline]){
                        menu_airline[count_airline] = new Array();
                    }
                    menu_airline[count_airline].push(info);
                    if(menu_airline[count_airline].length >= 10 && count_airline == 0){
                        count_airline++;
                    }
                }
            }
            //เมนูสายการบิน
            //เมนูระดับดาว
            for(let r in rating){
                var num = await tour.filter(x => (x.rating ? x.rating:0) == rating[r]).length;
                // var num = await tour.filter(x => rating.includes(x.rating)).length;
                if(num){
                    let info = {
                        name:rating[r]*1,
                        num:num,
                    }
                    menu_rating.push(info);
                }
            }
            //เมนูระดับดาว
            //เมนูจำนวนวัน
            for(let d in day_num){
                var num = await period.filter(x => x.day == day_num[d]);
                var check = new Array();
                    for(let n in num){
                        check.push(num[n].tour_id);
                    }
                    check = await check.filter((value, index, self) => {
                        return self.indexOf(value) === index;
                    });
                    if(check.length){
                        menu_day.push({
                            name : day_num[d],
                            num: check.length,
                            tour:check,
                        });
                    }
            }
            //เมนูจำนวนวัน
            //เมนูช่วงเดือน
                for(let year in month){
                    menu_month[year] = new Array();
                    for(let m in month[year]){
                        var num = await period.filter(x => x.group_date ==  month[year][m]+year && x.count > 0 && x.status_period != 3);
                        var check = new Array();
                        for(let n in num){
                            check.push(num[n].tour_id);
                            
                        }
                        check = await check.filter((value, index, self) => {
                            return self.indexOf(value) === index;
                        });
                        if(check.length){
                            menu_month[year].push({
                                id: month[year][m]+year,
                                name : month_data[month[year][m]*1],
                                num: check.length,
                                tour:check,
                                
                            });
                        }
                    }
                    if(!menu_month[year].length){
                        menu_month.splice(year);
                    }
                }
            //เมนูช่วงเดือน
            //เมนูวันหยุด
                for(let h in holiday){
                    var num = await period.filter(x => new Date(x.start_date).valueOf() >=  holiday[h].num_start*1000   && new Date(x.start_date).valueOf() <=  holiday[h].num_end*1000 );
                    var check = new Array();
                        for(let n in num){
                            check.push(num[n].tour_id);
                        }
                        check = await check.filter((value, index, self) => {
                            return self.indexOf(value) === index;
                        });
                        if(check.length){
                            menu_holiday.push({
                                id: holiday[h].id,
                                name : holiday[h].name,
                                num: check.length,
                                tour:check,
                                
                            });
                        }  
                }
                
            //เมนูวันหยุด
        }
        var country_act = 0;
        async function show_country(){
                var data = menu_country[0];
                var text = '';
                for(let y in data){
                    text = text+'<li><label class="check-container">'+data[y].name;
                    if(country_search == data[y].id*1){
                        text = text+'<input type="checkbox" checked id="country'+data[y].id+'" onclick="put_filter('+data[y].id+',`country`)" value="'+data[y].id+'">';
                        put_filter(data[y].id*1,`country`);
                    }else{
                        text = text+'<input type="checkbox" id="country'+data[y].id+'" onclick="put_filter('+data[y].id+',`country`)" value="'+data[y].id+'">';
                    }
                    text = text+'<span class="checkmark"></span><div class="count">('+data[y].num+')</div></label></li>';
                }
                var data = menu_country[1];
                if(data){
                    text = text+"<div id='moreprd' class='collapse'>";
                    for(let y in data){
                        text = text+'<li><label class="check-container">'+data[y].name;
                        if(country_search == data[y].id*1){
                            text = text+'<input type="checkbox" checked  id="country'+data[y].id+'" onclick="put_filter('+data[y].id+',`country`)" value="'+data[y].id+'">';
                            put_filter(data[y].id*1,`country`);
                        }else{
                            text = text+'<input type="checkbox"  id="country'+data[y].id+'" onclick="put_filter('+data[y].id+',`country`)" value="'+data[y].id+'">';
                        }
                        text = text+'<span class="checkmark"></span><div class="count">('+data[y].num+')</div></label></li>';
                    }
                    text = text+"</div>";
                    
                    if(data.length >= 1){
                        text = text+'<a data-bs-toggle="collapse" data-bs-target="#moreprd" class="seemore"> ดูเพิ่มเติม</a>';
                    }
                }

                if(isWin || isMac){    
                    document.getElementById('show_country').innerHTML = await text;
                }else if(isIPhone || isAndroid || isIPad){
                    document.getElementById('show_country_mb').innerHTML = await text;
                }
                if(text == ''){
                    $('#country-topic').hide();
                }
        }
        async function show_city(){
            var data = menu_city[0];
            var text = '';
           
            for(let y in data){
                text = text+'<li><label class="check-container">'+data[y].name;
                if(city_search == data[y].id*1){
                    text = text+'<input type="checkbox" checked id="city'+data[y].id+'" onclick="put_filter('+data[y].id+',`city`)" value="'+data[y].id+'">';
                    put_filter(data[y].id*1,`city`);
                }else{
                    text = text+'<input type="checkbox" id="city'+data[y].id+'" onclick="put_filter('+data[y].id+',`city`)" value="'+data[y].id+'">';
                }
                text = text+'<span class="checkmark"></span><div class="count">('+data[y].num+')</div></label></li>';
            }
            var data = menu_city[1];
            if(data){
                text = text+"<div id='morecity' class='collapse'>";
                for(let y in data){
                    text = text+'<li><label class="check-container">'+data[y].name;
                    if(city_search == data[y].id*1){
                        text = text+'<input type="checkbox" checked id="city'+data[y].id+'" onclick="put_filter('+data[y].id+',`city`)" value="'+data[y].id+'">';
                        put_filter(data[y].id*1,`city`);
                    }else{
                        text = text+'<input type="checkbox"  id="city'+data[y].id+'" onclick="put_filter('+data[y].id+',`city`)" value="'+data[y].id+'">';
                    }
                    text = text+'<span class="checkmark"></span><div class="count">('+data[y].num+')</div></label></li>';
                }
                text = text+"</div>";
                if(data.length >= 1){
                    text = text+'<a data-bs-toggle="collapse" data-bs-target="#morecity" class="seemore"> ดูเพิ่มเติม</a>';
                }
            }
            if(isWin || isMac){  
                document.getElementById('show_city').innerHTML = await text;
            }else if(isIPhone || isAndroid || isIPad){
                document.getElementById('show_city_mb').innerHTML = await text;
            }
            if(text == ''){
                $('#city-topic').hide();
            }
        }
        async function show_amupur(x){
           
                var data = menu_amupur[0];
                var text = '';
                var text_slide = '';
                for(let y in data){
                    text = text+'<li><label class="check-container">'+data[y].name;
                    text = text+'<input type="checkbox" id="amupur'+data[y].id+'" onclick="put_filter('+data[y].id+',`amupur`)" value="'+data[y].id+'">';
                    text = text+'<span class="checkmark"></span><div class="count">('+data[y].num+')</div></label></li>';
                }
                var data = menu_amupur[1];
                if(data){
                    text = text+"<div id='moreamupur' class='collapse'>";
                    for(let y in data){
                        text = text+'<li><label class="check-container">'+data[y].name;
                        text = text+'<input type="checkbox"  id="amupur'+data[y].id+'" onclick="put_filter('+data[y].id+',`amupur`)" value="'+data[y].id+'">';
                        text = text+'<span class="checkmark"></span><div class="count">('+data[y].num+')</div></label></li>';
                    }
                    text = text+"</div>";
                    if(data.length >= 1){
                        text = text+'<a data-bs-toggle="collapse" data-bs-target="#moreamupur" class="seemore"> ดูเพิ่มเติม</a>';
                    }
                }
                if(isWin || isMac){  
                    document.getElementById('show_amupur').innerHTML = await text;
                }else if(isIPhone || isAndroid || isIPad){
                    document.getElementById('show_amupur_mb').innerHTML = await text;
                }
                if(text == ''){
                    $('#amupur-topic').hide();
                    $('#amupur_input').hide();
                }
                 //ชื่อเมืองแสดงบนแบนเนอร์
                for(let c in city_slide){
                    text_slide = text_slide+ '<div class="item" ><a href="javascript:void(0);" onclick="document.getElementById(`amupur'+city_slide[c].id+'`).click()">';
                    text_slide = text_slide+ '<div class="catss">';
                    text_slide = text_slide+ 'ทัวร์'+city_slide[c].name;
                    text_slide = text_slide+ '</div></a></div>';
                }
                if(city_slide.length == 0){
                    $('#hide_slide').hide();
                }
                if(x){
                    document.getElementById('slide_province').innerHTML = await text_slide;
                }
               
              
        }
        async function show_price(){
            var data = menu_price;
            var text = '';
            for(let y in data){
                text = text+'<li><label class="check-container">'+data[y].name;
                if(price_search == data[y].id*1){
                    text = text+'<input type="checkbox" checked id="price'+data[y].id+'" onclick="put_filter('+data[y].id+',`price`)" value="'+data[y].id+'">';
                    put_filter(data[y].id*1,`price`);
                }else{
                    text = text+'<input type="checkbox" id="price'+data[y].id+'" onclick="put_filter('+data[y].id+',`price`)" value="'+data[y].id+'">';
                }
                text = text+'<span class="checkmark"></span><div class="count">('+data[y].num+')</div></label></li>';
               
            }
            if(isWin || isMac){  
                document.getElementById('show_price').innerHTML = await text;
            }else if(isIPhone || isAndroid || isIPad){
                document.getElementById('show_price_mb').innerHTML = await text;
            }
            if(text == ''){
                $('#price-topic').hide();
            }
        }
        async function show_airline(){
            var data = menu_airline[0];
            var text = '';
            for(let y in data){
                text = text+'<li><label class="check-container">'+data[y].name;
                text = text+'<input type="checkbox" id="airline'+data[y].id+'" onclick="put_filter('+data[y].id+',`airline`)" value="'+data[y].id+'">';
                text = text+'<span class="checkmark"></span><div class="count">('+data[y].num+')</div></label></li>';
            }
            var data = menu_airline[1];
            if(data){
                text = text+"<div id='moreairline' class='collapse'>";
                for(let y in data){
                    text = text+'<li><label class="check-container">';
                    if(data[y].img){
                        text = text+'<img src="/'+data[y].img+'" alt=""></img>';
                    }
                    text = text+' '+data[y].name;
                    text = text+'<input type="checkbox"  id="airline'+data[y].id+'" onclick="put_filter('+data[y].id+',`airline`)" value="'+data[y].id+'">';
                    text = text+'<span class="checkmark"></span><div class="count">('+data[y].num+')</div></label></li>';
                }
                text = text+"</div>";
                    if(data.length >= 1){
                        text = text+'<a data-bs-toggle="collapse" data-bs-target="#moreairline" class="seemore"> ดูเพิ่มเติม</a>';
                    }   
            }
            if(isWin || isMac){  
                document.getElementById('show_airline').innerHTML = await text;
            }else if(isIPhone || isAndroid || isIPad){
                document.getElementById('show_airline_mb').innerHTML = await text;
            }
            if(text == ''){
                $('#airline-topic').hide();
                $('#airline_input').hide();
            }
        }
        async function show_rating(){
            var data = menu_rating.sort((a, b) => a.name - b.name);
            var text = '';
            for(let y in data){
                text = text+'<li><label class="check-container"><div class="rating">';
                if(data[y].name != 0){
                    for(n=1;n<=data[y].name;n++){
                        text = text+'<i class="bi bi-star-fill"></i>';
                    }     
                }else{
                    text = text+'ไม่มีระดับดาวที่พัก';
                }
                text = text+'</div><input type="checkbox" id="rating'+data[y].name+'" onclick="put_filter('+data[y].name+',`rating`)" value="'+data[y].name+'">';
                text = text+'<span class="checkmark"></span><div class="count">('+data[y].num+')</div></label></li>';
            }
            if(isWin || isMac){  
                document.getElementById('show_rating').innerHTML = await text;
            }else if(isIPhone || isAndroid || isIPad){
                document.getElementById('show_rating_mb').innerHTML = await text;
            }
            if(text == ''){
                $('#rating-topic').hide();
            }
        }
        async function show_day(){
            var data = menu_day;
            var text = '';
            for(let y in data){
                text = text+'<li><label class="check-container">'+data[y].name+' วัน';
                text = text+'<input type="checkbox" id="day'+data[y].name+'" onclick="put_filter('+data[y].name+',`day`)" value="'+data[y].name+'">';
                text = text+'<span class="checkmark"></span><div class="count">('+data[y].num+')</div></label></li>';
            }
            if(isWin || isMac){ 
                document.getElementById('show_day').innerHTML = await text;
            }else if(isIPhone || isAndroid || isIPad){
                document.getElementById('show_day_mb').innerHTML = await text;
            }
            if(text == ''){
                $('#day-topic').hide();
            }
        }
        async function show_month(){
            var text = '';
            var month = menu_month;
            for(let year in month){
                text = text+'<li>'+year+'</li>';
                for(let m in month[year]){
                    text = text+'<li><label class="check-container">'+month[year][m].name;
                    text = text+'<input type="checkbox" id="month'+month[year][m].id+'" onclick="put_filter(`'+month[year][m].id+'`,`month`)" value="'+month[year][m].id+'">';
                    text = text+'<span class="checkmark"></span><div class="count">('+month[year][m].num+')</div></label></li>';
                }
            }
            if(isWin || isMac){ 
                document.getElementById('show_month').innerHTML = await text;
            }else if(isIPhone || isAndroid || isIPad){
                document.getElementById('show_month_mb').innerHTML = await text;
            }
            if(text == ''){
                $('#month-topic').hide();
            }
        }
        async function show_holiday(){
            var text = '';
            var data = menu_holiday;
            for(let y in data){
                    text = text+'<li><label class="check-container">'+data[y].name;
                    text = text+'<input type="checkbox" id="holiday'+data[y].id+'" onclick="put_filter(`'+data[y].id+'`,`holiday`)" value="'+data[y].id+'">';
                    text = text+'<span class="checkmark"></span><div class="count">('+data[y].num+')</div></label></li>';
                
            }
            if(isWin || isMac){ 
                document.getElementById('show_holiday').innerHTML = await text;
            }else if(isIPhone || isAndroid || isIPad){
                document.getElementById('show_holiday_mb').innerHTML = await text;
            }
            if(text == ''){
                $('#holiday-topic').hide();
            }
        }
        //ค้นหาอำเภอ
        async function find_amupur(){
            var keyword = document.getElementById('find_amupur').value;
            var find_data = new Array();
            var count_find = 0;
            if(keyword){
                var find_keyword = await amupur.filter(x=> x.name_th.indexOf(keyword) >= 0);
                if(find_keyword.length){
                    for(let f in find_keyword){
                        var num = await tour.filter(x => x.district_id.includes('"'+find_keyword[f].id+'"')).length;
                        if(num){
                            let info = {
                                id:find_keyword[f].id,
                                name:find_keyword[f].name_th != ''?find_keyword[f].name_th:find_keyword[f].name_en,
                                num:num,
                            }
                            if(!find_data[count_find]){
                                find_data[count_find] = new Array();
                            }
                            find_data[count_find].push(info);
                            if(find_data[count_find].length >= 10 && count_find == 0){
                                count_find++;
                            }
                        }
                    }
                    var data = menu_amupur[0];
                    var text = '';
                    for(let y in data){
                        text = text+'<li><label class="check-container">'+data[y].name;
                        text = text+'<input type="checkbox" id="amupur'+data[y].id+'" onclick="put_filter('+data[y].id+',`amupur`)" value="'+data[y].id+'">';
                        text = text+'<span class="checkmark"></span><div class="count">('+data[y].num+')</div></label></li>';
                    }
                    var data = menu_amupur[1];
                    if(data){
                        text = text+"<div id='moreamupur' class='collapse'>";
                        for(let y in data){
                            text = text+'<li><label class="check-container">'+data[y].name;
                            text = text+'<input type="checkbox"  id="amupur'+data[y].id+'" onclick="put_filter('+data[y].id+',`amupur`)" value="'+data[y].id+'">';
                            text = text+'<span class="checkmark"></span><div class="count">('+data[y].num+')</div></label></li>';
                        }
                        text = text+"</div>";
                        if(data.length >= 1){
                            text = text+'<a data-bs-toggle="collapse" data-bs-target="#moreamupur" class="seemore"> ดูเพิ่มเติม</a>';
                        }
                    }
                    if(isWin || isMac){
                        document.getElementById('show_amupur').innerHTML = await text;
                    }else if(isIPhone || isAndroid || isIPad){
                        document.getElementById('show_amupur_mb').innerHTML = await text;
                    }
                }else{
                    if(isWin || isMac){
                        document.getElementById('show_amupur').innerHTML = '<center><strong class="text-danger" >ไม่พบผลการค้นหา</strong></center>';
                    }else if(isIPhone || isAndroid || isIPad){
                        document.getElementById('show_amupur').innerHTML = '<center><strong class="text-danger">ไม่พบผลการค้นหา</strong></center>';
                    }
                }
            }else{
                var data = menu_amupur[0];
                var text = '';
                for(let y in data){
                    text = text+'<li><label class="check-container">'+data[y].name;
                    text = text+'<input type="checkbox" id="amupur'+data[y].id+'" onclick="put_filter('+data[y].id+',`amupur`)" value="'+data[y].id+'">';
                    text = text+'<span class="checkmark"></span><div class="count">('+data[y].num+')</div></label></li>';
                }
                var data = menu_amupur[1];
                if(data){
                    text = text+"<div id='moreamupur' class='collapse'>";
                    for(let y in data){
                        text = text+'<li><label class="check-container">'+data[y].name;
                        text = text+'<input type="checkbox"  id="amupur'+data[y].id+'" onclick="put_filter('+data[y].id+',`amupur`)" value="'+data[y].id+'">';
                        text = text+'<span class="checkmark"></span><div class="count">('+data[y].num+')</div></label></li>';
                    }
                    text = text+"</div>";
                    if(data.length >= 1){
                        text = text+'<a data-bs-toggle="collapse" data-bs-target="#moreamupur" class="seemore"> ดูเพิ่มเติม</a>';
                    }
                }
                if(isWin || isMac){
                    document.getElementById('show_amupur').innerHTML = await text;
                }else if(isIPhone || isAndroid || isIPad){
                    document.getElementById('show_amupur_mb').innerHTML = await text;
                }
            }
        }
        // ค้นหาสายการบิน
        async function find_airline(){
            var keyword = document.getElementById('find_airline').value;
            var find_data = new Array();
            var count_find = 0;
            if(keyword){
                var find_keyword = await airline.filter(x=> x.travel_name.indexOf(keyword) >= 0);
                if(find_keyword.length){
                    for(let f in find_keyword){
                        var num = await tour.filter(x => x.airline_id == find_keyword[f].id).length;
                        if(num){
                            let info = {
                                id:find_keyword[f].id,
                                name:find_keyword[f].travel_name,
                                num:num,
                                img:find_keyword[f].image,
                            }
                            if(!find_data[count_find]){
                                find_data[count_find] = new Array();
                            }
                            find_data[count_find].push(info);
                            if(find_data[count_find].length >= 10 && count_find == 0){
                                count_find++;
                            }
                        }
                    }
                    var data = find_data[0];
                    var text = '';
                    for(let y in data){
                        text = text+'<li><label class="check-container">'
                        if(data[y].img){
                            text = text+'<img src="/'+data[y].img+'" alt=""></img>';
                        }
                        text = text+' '+data[y].name;
                        text = text+'<input type="checkbox" id="airline'+data[y].id+'" onclick="put_filter('+data[y].id+',`airline`)" value="'+data[y].id+'">';
                        text = text+'<span class="checkmark"></span><div class="count">('+data[y].num+')</div></label></li>';
                    }
                    var data = find_data[1];
                    if(data){
                        text = text+"<div id='moreairline' class='collapse'>";
                        for(let y in data){
                            text = text+'<li><label class="check-container">';
                            if(data[y].img){
                                text = text+'<img src="/'+data[y].img+'" alt=""></img>';
                            }
                            text = text+' '+data[y].name;
                            text = text+'<input type="checkbox"  id="airline'+data[y].id+'" onclick="put_filter('+data[y].id+',`airline`)" value="'+data[y].id+'">';
                            text = text+'<span class="checkmark"></span><div class="count">('+data[y].num+')</div></label></li>';
                        }
                        text = text+"</div>";
                        if(data.length >= 1){
                            text = text+'<a data-bs-toggle="collapse" data-bs-target="#moreairline" class="seemore"> ดูเพิ่มเติม</a>';
                        }   
                    }
                    if(isWin || isMac){
                        document.getElementById('show_airline').innerHTML = await text;
                    }else if(isIPhone || isAndroid || isIPad){
                        document.getElementById('show_airline_mb').innerHTML = await text;
                    }
                }else{
                    if(isWin || isMac){
                        document.getElementById('show_airline').innerHTML = '<center><strong class="text-danger" >ไม่พบผลการค้นหา</strong></center>';
                    }else if(isIPhone || isAndroid || isIPad){
                        document.getElementById('show_airline_mb').innerHTML = '<center><strong class="text-danger">ไม่พบผลการค้นหา</strong></center>';
                    }
                }
            }else{
                var data = menu_airline[0];
                var text = '';
                for(let y in data){
                    text = text+'<li><label class="check-container">'
                    if(data[y].img){
                        text = text+'<img src="/'+data[y].img+'" alt=""></img>';
                    }
                    text = text+' '+data[y].name;
                    text = text+'<input type="checkbox" id="airline'+data[y].id+'" onclick="put_filter('+data[y].id+',`airline`)" value="'+data[y].id+'">';
                    text = text+'<span class="checkmark"></span><div class="count">('+data[y].num+')</div></label></li>';
                }
                var data = menu_airline[1];
                if(data){
                    text = text+"<div id='moreairline' class='collapse'>";
                    for(let y in data){
                        text = text+'<li><label class="check-container">';
                        if(data[y].img){
                            text = text+'<img src="/'+data[y].img+'" alt=""></img>';
                        }
                        text = text+' '+data[y].name;
                        text = text+'<input type="checkbox"  id="airline'+data[y].id+'" onclick="put_filter('+data[y].id+',`airline`)" value="'+data[y].id+'">';
                        text = text+'<span class="checkmark"></span><div class="count">('+data[y].num+')</div></label></li>';
                    }
                    text = text+"</div>";
                        if(data.length >= 1){
                            text = text+'<a data-bs-toggle="collapse" data-bs-target="#moreairline" class="seemore"> ดูเพิ่มเติม</a>';
                        }   
                }
                if(isWin || isMac){
                    document.getElementById('show_airline').innerHTML = await text;
                }else if(isIPhone || isAndroid || isIPad){
                    document.getElementById('show_airline_mb').innerHTML = await text;
                }
            }
        }
        async function put_filter (id,type){
         await check_date();
          if(type_data[type].includes(id)){
            var index = type_data[type].indexOf(id);
            type_data[type].splice(index,1);
            count_pagin = 1;
          await  check_month(index);
          await check_holiday(index);
          }else{
            type_data[type].push(id);
            count_pagin = 1;
          }  
         
          await filter_tour();
        }
       
        async function filter_tour (){
            data_tour = await tour;
            var count_tour = 0;
            var check_fill = true;
            // console.log(tour.length,'tour-check')
            if(type_data.country.length){
               for(let i in type_data.country){
                    // let data = await tour.filter(x => x.country_id.includes('"'+type_data.country[i]+'"'));
                    data_tour = await data_tour.filter(x => x.country_id.includes('"'+type_data.country[i]+'"'));
                    // array mearge
                    // data_tour = await data_tour.concat(data);
               }
               check_fill = false;
            }
            // console.log(data_tour.length,'data_tour-check1')
            if(type_data.price.length){
                for(let i in type_data.price){
                    //  let data = await tour.filter(x => x.price_group == type_data.price[i]);
                    //  data_tour = await data_tour.concat(data);
                    data_tour = await data_tour.filter(x => x.price_group == type_data.price[i]);
                }
                check_fill = false;
             }
            //  console.log(data_tour.length,'data_tour-check2')
            if(type_data.airline.length){
                for(let i in type_data.airline){
                    //  let data = await tour.filter(x => x.airline_id == type_data.airline[i]);
                    //  data_tour = await data_tour.concat(data);
                    data_tour =   await data_tour.filter(x => x.airline_id == type_data.airline[i]);
                }
                check_fill = false;
            }
            // console.log(data_tour.length,'data_tour-check3')
            if(type_data.rating.length){
                for(let i in type_data.rating){
                    // let data = await tour.filter(x => x.rating == type_data.rating[i]);
                    // data_tour = await data_tour.concat(data);
                    data_tour = await data_tour.filter(x => (x.rating ? x.rating:0) == type_data.rating[i]);
                }
                check_fill = false;
            }
            // console.log(data_tour.length,'data_tour-check4')
            if(type_data.day.length){
                for(let i in type_data.day){
                    let check = menu_day.find(x => x.name  ==  type_data.day[i]);
                    // let data = await tour.filter(x => check.tour.includes(x.id));
                    // data_tour = await data_tour.concat(data);
                    data_tour = await data_tour.filter(x => check.tour.includes(x.id));
                } 
                check_fill = false;
            }
            // console.log(data_tour.length,'data_tour-check5')
            if(type_data.holiday.length){
                for(let i in type_data.holiday){
                    let check = menu_holiday.find(x => x.id  ==  type_data.holiday[i]);
                    // let data = await tour.filter(x => check.tour.includes(x.id));
                    // data_tour = await data_tour.concat(data);
                    data_tour = await data_tour.filter(x => check.tour.includes(x.id));
                }
                check_fill = false;
            }
            // console.log(data_tour.length,'data_tour-check6')
            if(type_data.month.length){
                for(let i in type_data.month){
                    for(let m in menu_month){
                        let check = menu_month[m].find(x => x.id  ==  type_data.month[i]);
                        if(check){
                            // let data = await tour.filter(x => check.tour.includes(x.id));
                            // data_tour = await data_tour.concat(data);
                            data_tour = await data_tour.filter(x => check.tour.includes(x.id));
                        }
                       
                    }
                } 
                check_fill = false;
            }
            // console.log(data_tour.length,'data_tour-check7')
            if(type_data.city.length){
                for(let i in type_data.city){
                    //  let data = await tour.filter(x => x.city_id.includes('"'+type_data.city[i]+'"'));
                    //  // array mearge
                    //  data_tour = await data_tour.concat(data);
                    data_tour = await data_tour.filter(x => x.city_id.includes('"'+type_data.city[i]+'"'));
                }
                check_fill = false;
            }
            // console.log(data_tour.length,'data_tour-check8')
            if(type_data.amupur.length){
                for(let i in type_data.amupur){
                    //  let data = await tour.filter(x => x.district_id.includes('"'+type_data.amupur[i]+'"'));
                    //  // array mearge
                    //  data_tour = await data_tour.concat(data);
                    data_tour = await data_tour.filter(x => x.district_id.includes('"'+type_data.amupur[i]+'"'));
                }
                check_fill = false;
            }
            // console.log(data_tour.length,'data_tour-check9')
            if(type_data.start_date.length){
                var check_tour = new Array();
                if(start_search && end_search){
                  var  pe = await period.filter(x=> x.check_start >= str_start && x.check_start <= str_end);
                }else{
                  var  pe = await period.filter(x => new Date(x.start_date).valueOf() >= new Date(type_data.start_date).valueOf()  && new Date(x.start_date).valueOf() <=   new Date(type_data.end_date).valueOf());
                }
                for(let p in pe){
                    check_tour.push(pe[p].tour_id);
                    // pe_check.push(pe[p].id);
                    // let data = await tour.filter(x => x.id == pe[p].tour_id);
                    // data_tour = await data_tour.concat(data);
                }
                data_tour = await data_tour.filter(x => check_tour.includes(x.id));
               check_fill = false;
               
            }
            // console.log(data_tour.length,'data_tour-check10')
            // if(check_fill){
                // console.log(travel_search,'travel_search.length')
                // console.log(type_data.tour_code,'tour_code.length')
                // console.log(start_search,'start_search')
                // console.log(end_search,'end_search')
                if(type_data.travel_search.length){
                        // let data = await tour.filter(x => type_data.travel_search.includes(x.id));
                        // data_tour = await data_tour.concat(data);
                        data_tour = await data_tour.filter(x => type_data.travel_search.includes(x.id));
                        check_fill = false;
                        // console.log(data_tour,'travel_search.length')
                }if(type_data.tour_code.length){
                    // let data = await tour.filter(x => type_data.tour_code.includes(x.id));
                    // data_tour = await data_tour.concat(data);
                    data_tour = await data_tour.filter(x => type_data.tour_code.includes(x.id));
                    check_fill = false;
                }
                // else if(start_search && end_search){
                //     var search_check = new Array();
                //     var date_search = await period.filter(x=> x.check_start >= str_start && x.check_end <= str_end);
                //     for(let d in date_search){
                //         search_check.push(date_search[d].tour_id);
                //         // var date_tour = date_search[d].tour_id;
                //         // let data = await tour.filter(x => x.id == date_tour);
                //         // data_tour = await data_tour.concat(data);
                //     }
                //     data_tour = await data_tour.filter(x =>search_check.includes(x.id));
                // }
                if(check_fill){
                    data_tour = await tour;
                }
            // }
            // หา Unique
            // data_tour = await data_tour.filter((value, index, self) => {
            //     return self.indexOf(value) === index;
            // });
            if(document.getElementById('orderby_data2').value){
                var check_order = document.getElementById('orderby_data2').value;
            }else{
                var check_order = document.getElementById('orderby_data1').value;
            }
            if(check_order*1 != 0){
                OrderByData(check_order*1);
            //     if(check_order*1 == 1){
            //         await data_tour.sort((a, b) => a.price - b.price);
            //     }else if(check_order*1 == 2){
            //         await data_tour.sort((a, b) => b.tour_views - a.tour_views);
            //     }else if(check_order*1 == 3){
            //         data_tour = await tour.filter(x => special_price.includes(x.id));
            //     }else if(check_order*1 == 4){
            //         data_tour = await tour.filter(x => promotion.includes(x.id));
            //     }
            }else{
                main_tour = new Array();
                for(let d in data_tour){
                    if(!main_tour[count_tour]){
                        main_tour[count_tour] = new Array();
                    }
                    if(type_data.start_date.length){
                        var period_data = await period.filter(x=> x.tour_id == data_tour[d].id  && x.count > 0 && x.status_period != 3 && new Date(x.start_date).valueOf() >= new Date(type_data.start_date).valueOf()  && new Date(x.start_date).valueOf() <=   new Date(type_data.end_date).valueOf());
                        var period_soldout = await period.filter(x=> x.tour_id == data_tour[d].id && x.count == 0 && x.status_period == 3 && new Date(x.start_date).valueOf() >= new Date(type_data.start_date).valueOf()  && new Date(x.start_date).valueOf() <=   new Date(type_data.end_date).valueOf());
                    }else{
                        var period_data = await period.filter(x=> x.tour_id == data_tour[d].id  && x.count > 0 && x.status_period != 3);
                        var period_soldout = await period.filter(x=> x.tour_id == data_tour[d].id && x.count == 0 && x.status_period == 3);
                    }
                    // let period_data = await period.filter(x=> x.tour_id == data_tour[d].id && x.count > 0 && x.status_period != 3);
                    let group_period = new Array();
                    for(let p in period_data){
                        if(!group_period[period_data[p].group_date]){
                            group_period[period_data[p].group_date] = new Array;
                        }
                        group_period[period_data[p].group_date].push(period_data[p])
                    }
                    let country_data = JSON.parse(data_tour[d].province_id);
                    var country_fil = new Array();
                    for(let t in country_data){
                        country_fil =  await province.filter(x => x.id == country_data[t]);
                    }
                    let info = {
                        tour:data_tour[d],
                        airline: await airline.find(x=> x.id == data_tour[d].airline_id),
                        period: group_period,
                        tour_type: await tour_type.find(x=>x.id == data_tour[d].type_id),
                        sold_out: period_soldout,
                        country:country_fil,
                        // period: await period.filter(x=> x.tour_id == data_tour[d].id && x.count > 0 && x.status_period != 3),
                    }
                    main_tour[count_tour].push(info);
                    if(main_tour[count_tour].length >= 15){
                        count_tour++;
                    }
                    
                }
                document.getElementById('show_total').innerHTML = 'พบ '+data_tour.length+' รายการ';
                total_page = count_tour;
                await show_tour(0);
                // readMore();
                await SelectFilter();
            }
            
          }
          var count_pagin = 1;
          async function show_tour(x){
            if(x == undefined){
                count_pagin++;
            }

            // $('#page'+paginat_act).removeClass('pagination-active');
            // $('#page'+(x+1)).addClass('pagination-active');
            // paginat_act = x;
            // await count_paginate(paginat_act);
            var tour_show = main_tour[count_pagin-1];
            if(tour_show && (typeof tour_show === 'string' || (typeof tour_show === 'object' && tour_show.length >= 15)) && main_tour.length > 1){
                $('#btn-showmore').removeClass('d-none');
            }else{
                $('#btn-showmore').addClass('d-none');
            }
            var text = '';
            var text_grid = '';
            for(let y in tour_show){
                var order_period = Object.keys(tour_show[y].period).sort((a, b) => a - b);
                text +=            "<div class='boxwhiteshd'>";
                text +=                "<div class='toursmainshowGroup  hoverstyle'>";
                text +=                    "<div class='row'>";
                text +=                        "<div class='col-lg-12 col-xl-3 pe-xl-0'>";
                text +=                            "<div class='covertourimg'>";
                text +=                                "<figure>";
                text +=                                    "<a href='/tour/"+tour_show[y].tour.slug+"' target='_blank'><img src='/"+tour_show[y].tour.image+"' alt=''></a>";
                text +=                                "</figure>";
                text +=                                "<div class='d-block d-sm-block d-md-block d-lg-none d-xl-none'>";
                text +=                                    "<a href='/tour/"+tour_show[y].tour.slug+"' target='_blank' class='tagicbest'>";
                                                            if(tour_show[y].tour_type){
                text +=                                          "<img src='/"+tour_show[y].tour_type.image+"' class='img-fluid' alt=''>";
                                                            }
                text +=                                     "</a>";
                text +=                                "</div>";
                                                    if(tour_show[y].tour.special_price > 0){
                text +=                                 "<div class='saleonpicbox'>";
                text +=                                    "<span> ลดราคาพิเศษ</span> <br>";
                text +=                                    Intl.NumberFormat('th-TH', {currency:'THB',}).format(tour_show[y].tour.special_price)+" บาท";  
                text +=                                 "</div>";
                                                    }
                                                    if(Object.keys(tour_show[y].period).length === 0){
                text +=                                 "<div class='soldfilter'>";
                text +=                                     "<div class='soldop'>";
                text +=                                         "<span class='bigSold'>SOLD OUT </span> <br>";
                text +=                                         "<span class='textsold'> ว้า! หมดแล้ว คุณตัดสินใจช้าไป</span> <br>";
                text +=                                         "<a href='/tour/"+tour_show[y].tour.slug+"' target='_blank' class='btn btn-second mt-3'><i class='fi fi-rr-search'></i> หาโปรแกรมทัวร์ใกล้เคียง</a>";
                text +=                                     "</div>";
                text +=                                 "</div>";
                                                    }
                text +=                             "<div class='priceonpic'>";
                                                    if(tour_show[y].tour.special_price > 0){
                                                        var total_price = tour_show[y].tour.price - tour_show[y].tour.special_price ;
                text +=                                    "<span class='originalprice'>ปกติ "+Intl.NumberFormat('th-TH', {currency:'THB',}).format(tour_show[y].tour.price)+" </span><br>";
                text +=                                    "เริ่ม<span class='saleprice'> "+Intl.NumberFormat('th-TH', {currency:'THB',}).format(total_price)+" บาท</span>";
                                                    }else{
                text +=                                    "<span class='saleprice'> "+Intl.NumberFormat('th-TH', {currency:'THB',}).format(tour_show[y].tour.price)+" บาท</span>";
                                                    }
                text +=                             "</div>";
                text +=                             "<div class='addwishlist'>";
                text +=                                  "<a href='javascript:void(0);' class='wishlist' data-tour-id='"+tour_show[y].tour.id+"'><i class='bi bi-heart-fill' id='likeButton' onclick='likedTour("+tour_show[y].tour.id+")'></i></a>";
                text +=                             "</div>";
                text +=                           "</div>";
                text +=                         "</div>";
                text +=                        "<div class='col-lg-12 col-xl-9'>";
                text +=                            "<div class='codeandhotel Cropscroll mt-1'>";
                                                       if(tour_show[y].country){
                text +=                                "<li class='bgwhite'><a href='/oversea/"+tour_show[y].country[0].slug+"'><i class='fi fi-rr-marker' style='color:#f15a22;'>";
                                                            for(let c in tour_show[y].country){
                text +=                                         tour_show[y].country[c].name_th?tour_show[y].country[c].name_th:tour_show[y].country[c].name_en;
                                                            }
                text +=                                 "</a></i>";
                                                        }
                text +=                                "<li>รหัสทัวร์ : <span class='bluetext'>";
                                                            if(tour_show[y].tour.code1_check){ 
                text +=                                         tour_show[y].tour.code1;
                                                            }else{ 
                text +=                                         tour_show[y].tour.code;
                                                            }
                text +=                                     "</span>";
                text +=                                "</li>";
                text +=                                "<li class='rating'>โรงแรม"; 
                                                         if(tour_show[y].tour.rating > 0){
                text +=                                         "<a href='javascript:void(0);' onclick='document.getElementById(`rating"+tour_show[y].tour.rating+"`).click()'>";
                                                                for($i=1; $i <= tour_show[y].tour.rating; $i++){
                text +=                                             "<i class='bi bi-star-fill'></i>";
                                                                }
                text +=                                         "</a>";
                                                        }else{
                text +=                                         "<a href='javascript:void(0);' onclick='document.getElementById(`rating0`).click()'></a>";
                                                        }  
                text +=                                "</li>";
                                                        if(tour_show[y].airline){
                text +=                                  "<li>สายการบิน <a href='javascript:void(0);' onclick='document.getElementById(`airline"+tour_show[y].airline.id+"`).click()' >";
                                                            if(tour_show[y].airline.image){
                text +=                                        "<img src='/"+tour_show[y].airline.image+"' alt=''></a></li>";
                                                            }                                      
                text +=                                  "</li>";
                                                        }
                text +=                                "<li>";
                text +=                                    "<div  class='d-none d-sm-none d-md-none d-lg-block d-xl-block'>";
                                                            if(tour_show[y].tour_type){
                text +=                                         "<a href='javascript:void(0);' class='tagicbest' onclick='OrderByType("+tour_show[y].tour_type.id+")'><img src='/"+tour_show[y].tour_type.image+"' class='img-fluid' alt=''></a>";
                                                            }
                text +=                                    "</div>";
                text +=                                "</li>";
                                                        if(Object.keys(tour_show[y].period).length > 0){
                                                                for(let dp in tour_show[y].period){
                                                                    var day_num = tour_show[y].period[dp][0].day;
                                                                }
                text +=                                 "<li class='bgor'>ระยะเวลา <a href='javascript:void(0);' onclick='document.getElementById(`day"+day_num+"`).click()'>"+tour_show[y].tour.num_day+"</a></li>";
                                                        }
                text +=                           " </div>";
                text +=                            "<div class='nameTop'>";
                text +=                                "<h3> <a href='/tour/"+tour_show[y].tour.slug+"' target='_blank' >"+tour_show[y].tour.name+"</a></h3>";
                text +=                            "</div>";
                text +=                            "<div class='pricegroup text-end'>";
                                                    if(tour_show[y].tour.special_price > 0){
                                                        var total_price = tour_show[y].tour.price - tour_show[y].tour.special_price; 
                text +=                                     "<span class='originalprice'>ปกติ "+Intl.NumberFormat('th-TH', {currency:'THB',}).format(tour_show[y].tour.price)+" </span><br>";
                text +=                                     "เริ่ม<span class='saleprice'> "+Intl.NumberFormat('th-TH', {currency:'THB',}).format(total_price)+" บาท</span>";
                                                    }else{
                text +=                                     "<span class='saleprice'> "+Intl.NumberFormat('th-TH', {currency:'THB',}).format(tour_show[y].tour.price)+" บาท</span>";
                                                    }   
                text +=                            "</div>";
                                                    if(tour_show[y].tour.description){
                text +=                                 "<div class='highlighttag'>";
                text +=                                     "<span><i class='fi fi-rr-tags'></i> </span>"+tour_show[y].tour.description;
                text +=                                 "</div>";
                                                    }
                                    var count_hilight = 0;
                                    if(tour_show[y].tour.travel){ count_hilight++; }
                                    if(tour_show[y].tour.shop){ count_hilight++; }
                                    if(tour_show[y].tour.eat){  count_hilight++; }
                                    if(tour_show[y].tour.special){ count_hilight++; }
                                    if(tour_show[y].tour.stay){  count_hilight++; }
                                    
                text +=                         "<div class='hilight mt-2'>";
                text +=                              "<div class='readMore'>";
                text +=                                   "<div class='readMoreWrapper'>";
                text +=                                       "<div class='readMoreText2'>";
                                                                if(tour_show[y].tour.travel){
                text +=                                             "<li>";
                text +=                                                 "<div class='iconle'><span><i class='bi bi-camera-fill'></i></span></div>";
                text +=                                                 "<div class='topiccenter'><b>เที่ยว</b></div>";
                text +=                                                 "<div class='details'>"+tour_show[y].tour.travel+"</div>";
                text +=                                             "</li>";
                                                                }
                                                                if(tour_show[y].tour.shop){
                text +=                                            "<li>";
                text +=                                                "<div class='iconle'><span><i  class='bi bi-bag-fill'></i></span></div>";
                text +=                                                "<div class='topiccenter'><b>ช้อป </b></div>";
                text +=                                                "<div class='details'> "+tour_show[y].tour.shop+"</div>";
                text +=                                            "</li>";
                                                                }
                                                                if(tour_show[y].tour.eat){
                text +=                                            "<li>";
                text +=                                                "<div class='iconle'><span><svg  xmlns='http://www.w3.org/2000/svg' width='22' height='22' fill='currentColor' class='bi bi-cup-hot-fill' viewBox='0 0 16 16'>";
                text +=                                                            "<path fill-rule='evenodd' d='M.5 6a.5.5 0 0 0-.488.608l1.652 7.434A2.5 2.5 0 0 0 4.104 16h5.792a2.5 2.5 0 0 0 2.44-1.958l.131-.59a3 3 0 0 0 1.3-5.854l.221-.99A.5.5 0 0 0 13.5 6H.5ZM13 12.5a2.01 2.01 0 0 1-.316-.025l.867-3.898A2.001 2.001 0 0 1 13 12.5Z' />";
                text +=                                                            "<path  d='m4.4.8-.003.004-.014.019a4.167 4.167 0 0 0-.204.31 2.327 2.327 0 0 0-.141.267c-.026.06-.034.092-.037.103v.004a.593.593 0 0 0 .091.248c.075.133.178.272.308.445l.01.012c.118.158.26.347.37.543.112.2.22.455.22.745 0 .188-.065.368-.119.494a3.31 3.31 0 0 1-.202.388 5.444 5.444 0 0 1-.253.382l-.018.025-.005.008-.002.002A.5.5 0 0 1 3.6 4.2l.003-.004.014-.019a4.149 4.149 0 0 0 .204-.31 2.06 2.06 0 0 0 .141-.267c.026-.06.034-.092.037-.103a.593.593 0 0 0-.09-.252A4.334 4.334 0 0 0 3.6 2.8l-.01-.012a5.099 5.099 0 0 1-.37-.543A1.53 1.53 0 0 1 3 1.5c0-.188.065-.368.119-.494.059-.138.134-.274.202-.388a5.446 5.446 0 0 1 .253-.382l.025-.035A.5.5 0 0 1 4.4.8Zm3 0-.003.004-.014.019a4.167 4.167 0 0 0-.204.31 2.327 2.327 0 0 0-.141.267c-.026.06-.034.092-.037.103v.004a.593.593 0 0 0 .091.248c.075.133.178.272.308.445l.01.012c.118.158.26.347.37.543.112.2.22.455.22.745 0 .188-.065.368-.119.494a3.31 3.31 0 0 1-.202.388 5.444 5.444 0 0 1-.253.382l-.018.025-.005.008-.002.002A.5.5 0 0 1 6.6 4.2l.003-.004.014-.019a4.149 4.149 0 0 0 .204-.31 2.06 2.06 0 0 0 .141-.267c.026-.06.034-.092.037-.103a.593.593 0 0 0-.09-.252A4.334 4.334 0 0 0 6.6 2.8l-.01-.012a5.099 5.099 0 0 1-.37-.543A1.53 1.53 0 0 1 6 1.5c0-.188.065-.368.119-.494.059-.138.134-.274.202-.388a5.446 5.446 0 0 1 .253-.382l.025-.035A.5.5 0 0 1 7.4.8Zm3 0-.003.004-.014.019a4.077 4.077 0 0 0-.204.31 2.337 2.337 0 0 0-.141.267c-.026.06-.034.092-.037.103v.004a.593.593 0 0 0 .091.248c.075.133.178.272.308.445l.01.012c.118.158.26.347.37.543.112.2.22.455.22.745 0 .188-.065.368-.119.494a3.198 3.198 0 0 1-.202.388 5.385 5.385 0 0 1-.252.382l-.019.025-.005.008-.002.002A.5.5 0 0 1 9.6 4.2l.003-.004.014-.019a4.149 4.149 0 0 0 .204-.31 2.06 2.06 0 0 0 .141-.267c.026-.06.034-.092.037-.103a.593.593 0 0 0-.09-.252A4.334 4.334 0 0 0 9.6 2.8l-.01-.012a5.099 5.099 0 0 1-.37-.543A1.53 1.53 0 0 1 9 1.5c0-.188.065-.368.119-.494.059-.138.134-.274.202-.388a5.446 5.446 0 0 1 .253-.382l.025-.035A.5.5 0 0 1 10.4.8Z' />";
                text +=                                                        "</svg></span> </div>";
                text +=                                                "<div class='topiccenter'><b>กิน </b></div>";
                text +=                                                 "<div class='details'>"+tour_show[y].tour.eat+"</div>";
                text +=                                            "</li>";
                                                                }
                                                                if(tour_show[y].tour.special){
                text +=                                            "<li>";
                text +=                                                "<div class='iconle'><span><i  class='bi bi-bookmark-heart-fill'></i></span> </div>";
                text +=                                                "<div class='topiccenter'><b>พิเศษ </b></div>";
                text +=                                                "<div class='details'> "+tour_show[y].tour.special+"</div>";
                text +=                                            "</li>";
                                                                }
                                                                if(tour_show[y].tour.stay){
                text +=                                            "<li>";
                text +=                                                "<div class='iconle'><span><svg  xmlns='http://www.w3.org/2000/svg'  width='22' height='22'   fill='currentColor' class='bi bi-buildings-fill' viewBox='0 0 16 16'>";
                text +=                                                            "<path d='M15 .5a.5.5 0 0 0-.724-.447l-8 4A.5.5 0 0 0 6 4.5v3.14L.342 9.526A.5.5 0 0 0 0 10v5.5a.5.5 0 0 0 .5.5h9a.5.5 0 0 0 .5-.5V14h1v1.5a.5.5 0 0 0 .5.5h3a.5.5 0 0 0 .5-.5V.5ZM2 11h1v1H2v-1Zm2 0h1v1H4v-1Zm-1 2v1H2v-1h1Zm1 0h1v1H4v-1Zm9-10v1h-1V3h1ZM8 5h1v1H8V5Zm1 2v1H8V7h1ZM8 9h1v1H8V9Zm2 0h1v1h-1V9Zm-1 2v1H8v-1h1Zm1 0h1v1h-1v-1Zm3-2v1h-1V9h1Zm-1 2h1v1h-1v-1Zm-2-4h1v1h-1V7Zm3 0v1h-1V7h1Zm-2-2v1h-1V5h1Zm1 0h1v1h-1V5Z' />";
                text +=                                                        "</svg></span> </div>";
                text +=                                                "<div class='topiccenter'><b>พัก </b></div>";
                text +=                                                "<div class='details'>"+tour_show[y].tour.stay+"</div>";
                text +=                                            "</li>";
                                                                }
                text +=                                        "</div>";
                text +=                                        "<div class='readMoreGradient'></div>";
                text +=                                    "</div>";
                                                            if(count_hilight > 0){
                text +=                                    "<a class='readMoreBtn2'></a>";
                text +=                                    "<span class='readLessBtnText' style='display: none;'>Read Less</span>";
                text +=                                    "<span class='readMoreBtnText'  style='display: none;'>Read More </span>";
                                                            }
                text +=                                "</div>";
                text +=                            "</div>";
                text +=                        "</div>";
                text +=                    "</div>";
                                 
                                    if(Object.keys(tour_show[y].period).length > 0){
                text +=                    "<div class='periodtime'>";
                text +=                        "<div class='readMore'>";
                text +=                            "<div class='readMoreWrapper'>";
                text +=                                "<div class='readMoreText'>";
                text +=                                    "<div class='listperiod_moredetails'>";
                                                            for(let gp in order_period){
                                                                var month_datas = order_period[gp].split('202');
                text +=                                        "<div class='tagmonth'>";
                text +=                                            "<span class='month'>"+month_period[Number(month_datas[0])]+"</span>";
                text +=                                       "</div>";
                text +=                                       "<div class='splgroup'>";
                                                                    for(pd in tour_show[y].period[order_period[gp]]){
                                                                        var date_start = new Date(tour_show[y].period[order_period[gp]][pd].start_date);
                                                                        var date_end = new Date(tour_show[y].period[order_period[gp]][pd].end_date);
                text +=                                                 "<li>";
                                                                            if(tour_show[y].period[order_period[gp]][pd].period_date > 0 && tour_show[y].period[order_period[gp]][pd].count > 10){
                text +=                                                         "<a  data-tooltip='"+tour_show[y].period[order_period[gp]][pd].period_date+" วัน' class='staydate'>"+tour_show[y].period[order_period[gp]][pd].count_holiday+"</a>";
                                                                            } 
                                                                            if(tour_show[y].period[order_period[gp]][pd].count <= 10){
                text +=                                                          "<span class='fulltext'>*</span>";
                                                                            }
                                                                            if(tour_show[y].period[order_period[gp]][pd].period_date == 0 && tour_show[y].period[order_period[gp]][pd].count > 10){
                                                                                if(tour_show[y].period[order_period[gp]][pd].special_price1 > 0){
                                                                                    var check_price = tour_show[y].period[order_period[gp]][pd].price1 - tour_show[y].period[order_period[gp]][pd].special_price1;
                                                                                }else{
                                                                                    var check_price = tour_show[y].period[order_period[gp]][pd].price1;
                                                                                }
                text +=                                                          "<span class='saleperiod'>"+Intl.NumberFormat('th-TH', {currency:'THB',}).format(check_price)+"฿ </span>";
                                                                            } 
                text +=                                                     "<br>";
                text +=                                                     date_start.getDate()+" - "+date_end.getDate(); 
                text +=                                                 "</li>";
                                                                    }    
                                                                                                       
                                                            
                text +=                                        "</div>";
                text +=                                        "<hr>";
                                                            }
                text +=                                    "</div>";
                text +=                                "</div>";
                text +=                                "<div class='readMoreGradient'></div>";
                text +=                            "</div>";
                                            if(Object.keys(tour_show[y].period).length > 1){
                text +=                            "<a class='readMoreBtn'></a>";
                text +=                            "<span class='readLessBtnText' style='display: none;'>Read Less</span>";
                text +=                            "<span class='readMoreBtnText' style='display: none;'>Read More</span>";
                                            }
                text +=                        "</div>";
                text +=                    "</div>";
                                        
                text +=                    "<div class='remainsFull'>";
                text +=                        "<li>* ใกล้เต็ม</li>";
                text +=                        "<li><span class='noshowpad'>-</span>";
                text +=                            "<span class='showpad'>-</span> จำนวนวันหยุด</li>";
                text +=                    "</div>";
                text +=                    "<div class='row'>";
                text +=                        "<div class='col-md-9'>";
                                                if(tour_show[y].sold_out.length){
                text +=                            "<div class='fullperiod'>";
                text +=                                "<h6>พีเรียดที่เต็มแล้ว ("+tour_show[y].sold_out.length+")</h6>";
                                                        for(let s in tour_show[y].sold_out){
                                                            var dateS_soldout = new Date(tour_show[y].sold_out[s].start_date);
                                                            var dateE_soldout = new Date(tour_show[y].sold_out[s].end_date);
                text +=                                         "<span class='monthsold'>"+month_period[dateS_soldout.getMonth()+1]+"</span>";
                text +=                                         "<li>"+dateS_soldout.getDate()+" - "+dateE_soldout.getDate()+"</li>";
                                                        }
                text +=                            "</div>";
                                                }
                
                text +=                        "</div>";
                text +=                        "<div class='col-md-3 text-md-end'>";
                text +=                            "<a href='/tour/"+tour_show[y].tour.slug+"' target='_blank' class='btn-main-og  morebtnog'>รายละเอียด</a>";
                text +=                        "</div>";
                text +=                         "<br><br>";
                text +=                    "</div>";
                                        }
                text +=                "</div>";
                text +=            "</div>";
                
                // grid view 
                    if(Object.keys(tour_show[y].period).length > 0){
                        text_grid +=            "<tr>";
                        text_grid +=                        "<td>";
                        text_grid +=                            "<div class='row'>";
                        text_grid +=                                "<div class='col-5 col-lg-4'>";
                        text_grid +=                                   " <a href='/tour/"+tour_show[y].tour.slug+"' target='_blank'><img src='/"+tour_show[y].tour.image+"' class='img-fluid' alt=''></a>";
                        text_grid +=                                "</div>";
                        text_grid +=                                "<div class='col-7 col-lg-8 titlenametab'>";
                        text_grid +=                                    "<h3><a href='/tour/"+tour_show[y].tour.slug+"' target='_blank'>"+tour_show[y].tour.name+"</a> </h3>";
                        text_grid +=                                "</div>";
                        text_grid +=                            "</div>";
                        text_grid +=                        "</td>";
                                                            if(tour_show[y].country){
                        text_grid +=                        "<td><a href='/oversea/"+tour_show[y].country[0].slug+"' target='_blank'>";
                                                                for(let c in tour_show[y].country){
                        text_grid +=                                   tour_show[y].country[c].name_th?tour_show[y].country[c].name_th:tour_show[y].country[c].name_en;
                                                                }
                        text_grid +=                        "</a> </td>";
                                                            }
                        text_grid +=                        "<td>";
                                                            if(Object.keys(tour_show[y].period).length > 0){
                                                                for(let dp in tour_show[y].period){
                                                                    var day_num = tour_show[y].period[dp][0].day;
                                                                }
                        text_grid +=                        "<a href='javascript:void(0);' onclick='document.getElementById(`day"+day_num+"`).click()'>"+tour_show[y].tour.num_day+"</a>";                  
                                                            }
                        text_grid +=                        "</td>";
                        text_grid +=                        "<td>";
                                                                for(let gp in order_period){
                        text_grid +=                                 "<a href='javascript:void(0);' onclick='document.getElementById(`month"+order_period[gp]+"`).click()'>"+month_period[new Date(tour_show[y].period[order_period[gp]][0].start_date).getMonth()+1]+"</a>";    
                                                                    break;
                                                                }
                        text_grid +=                        "</td>";
                        text_grid +=                        "<td>";
                                                            if(tour_show[y].airline){
                                                                "<a href='javascript:void(0);' onclick='document.getElementById(`airline"+tour_show[y].tour.airline_id+"`).click()'>";
                        text_grid +=                               "<img src='/"+tour_show[y].airline.image+"' class='img-fluid' alt=''>";
                        text_grid +=                            "</a>";
                                                            }
                        text_grid +=                        "</td>";
                        text_grid +=                        "<td>";
                                                                if(tour_show[y].tour.special_price > 0){
                                                                    var total_price = tour_show[y].tour.price - tour_show[y].tour.special_price; 
                        text_grid +=                                     "เริ่ม "+Intl.NumberFormat('th-TH', {currency:'THB',}).format(total_price)+" บาท";
                                                                }else{
                        text_grid +=                                     "เริ่ม "+Intl.NumberFormat('th-TH', {currency:'THB',}).format(tour_show[y].tour.price)+" บาท";
                                                                }   
                        text_grid +=                        "</td>";
                        text_grid +=                         "<td>";
                        text_grid +=                            "<div class='rating'>";
                                                                    if(tour_show[y].tour.rating > 0){
                        text_grid +=                                        "<a href='javascript:void(0);' onclick='document.getElementById(`rating"+tour_show[y].tour.rating+"`).click()'>";
                                                                            for($i=1; $i <= tour_show[y].tour.rating; $i++){
                        text_grid +=                                             "<i class='bi bi-star-fill'></i>";
                                                                            }
                        text_grid +=                                         "</a>";
                                                                    }else{
                        text_grid +=                                         "<a href='javascript:void(0);' onclick='document.getElementById(`rating0`).click()'></a>";
                                                                    }     
                        text_grid +=                             "</div>";
                        text_grid +=                        "</td>";
                        text_grid +=                        "<td>";
                                                            if(tour_show[y].tour_type){
                                                                "<a href='javascript:void(0);' onclick='OrderByType("+tour_show[y].tour_type.id+")' target='_blank' class='tagicbest'>";
                        text_grid +=                                "<img src='/"+tour_show[y].tour_type.image+"' class='img-fluid' alt=''>";
                        text_grid +=                            "</a>";
                                                            }
                        text_grid +=                        "</td>";
                        text_grid +=                       " <td><a href='/tour/"+tour_show[y].tour.slug+"' target='_blank' class='link'><i  class='bi bi-chevron-right'></i></a></td>";
                        text_grid +=                "</tr>";
                    }
                // grid view   
            }
            // document.getElementById('show_tour').innerHTML = text;
            // document.getElementById('show_grid').innerHTML = text_grid;
            if(x != undefined){
                document.getElementById('show_tour').innerHTML = text;
                document.getElementById('show_grid').innerHTML = text_grid;
            }else{
                $('#show_tour').append(text);
                $('#show_grid').append(text_grid);
            }
          
            await readMore();
      
        }
        // ฟิลเตอร์ที่เลือก
        async function SelectFilter(){
        var text = '';
        for(let x in type_data){
            if(type_data[x].length){
                if(x == 'price'){
                    for(let y in type_data[x]){
                        text += "<li onclick='document.getElementById(`"+x+type_data[x][y]+"`).click()'><label class='check-container'>"+price[type_data[x][y]]+"  <i class='fa fa-times-circle' aria-hidden='true'></i></label></li>";      
                    }
                }
                if(x == 'month'){
                    for(let y in type_data[x]){
                        let month_value = type_data[x][y];
                        let m =  month_value.substr(0,2)*1;
                        let year =  month_value.substr(2,5);
                        text += "<li onclick='document.getElementById(`"+x+month_value+"`).click()'><label class='check-container'>"+month_data[m]+' '+year+"  <i class='fa fa-times-circle' aria-hidden='true'></i></label></li>";      
                    }
                   await check_month(type_data[x]);
                }
                if(x == 'holiday'){
                    for(let y in type_data[x]){
                        let day = holiday.find(z=>z.id == type_data[x][y]*1);
                        text += "<li onclick='document.getElementById(`"+x+type_data[x][y]+"`).click()'><label class='check-container'>"+day.name+"  <i class='fa fa-times-circle' aria-hidden='true'></i></label></li>";      
                    }
                   await check_holiday(type_data[x]);
                }
                if(x == 'country'){
                    for(let y in type_data[x]){
                        let country_data = country.find(z=>z.id == type_data[x][y]);
                        let name = country_data.country_name_th != ''?country_data.country_name_th:country_data.country_name_en;
                        text += "<li onclick='document.getElementById(`"+x+type_data[x][y]+"`).click()'><label class='check-container'>"+name+"  <i class='fa fa-times-circle' aria-hidden='true'></i></label></li>";      
                    }
                }
                if(x == 'day'){
                    for(let y in type_data[x]){
                        text += "<li onclick='document.getElementById(`"+x+type_data[x][y]+"`).click()'><label class='check-container'>"+type_data[x][y]+" วัน <i class='fa fa-times-circle' aria-hidden='true'></i></label></li>";      
                    }
                }
                if(x == 'airline'){
                    for(let y in type_data[x]){
                        let airline_data = airline.find(z=>z.id == type_data[x][y]);
                        text += "<li onclick='document.getElementById(`"+x+type_data[x][y]+"`).click()'><label class='check-container'>"+airline_data.travel_name+" <i class='fa fa-times-circle' aria-hidden='true'></i></label></li>";      
                    }
                }
                if(x == 'rating'){
                    for(let y in type_data[x]){
                        text += "<li onclick='document.getElementById(`"+x+type_data[x][y]+"`).click()'><label class='check-container'>";
                        if(type_data[x][y] != 0){
                            text += type_data[x][y]+' ดาว';
                        }else{
                            text += 'ไม่มีระดับดาวที่พัก';
                        }   
                        text += " <i class='fa fa-times-circle' aria-hidden='true'></i></label></li>";   
                    }
                }
                if(x == 'city'){
                    for(let y in type_data[x]){
                        let city_data = city.find(z=>z.id == type_data[x][y]);
                        let name = city_data.city_name_th != ''?city_data.city_name_th:city_data.city_name_en;;
                        text += "<li onclick='document.getElementById(`"+x+type_data[x][y]+"`).click()'><label class='check-container'>"+name+"  <i class='fa fa-times-circle' aria-hidden='true'></i></label></li>";      
                    }
                }
                if(x == 'amupur'){
                    for(let y in type_data[x]){
                        let amupur_data = amupur.find(z=>z.id == type_data[x][y]);
                        let name = amupur_data.name_th != ''?amupur_data.name_th:amupur_data.name_en;;
                        text += "<li onclick='document.getElementById(`"+x+type_data[x][y]+"`).click()'><label class='check-container'>"+name+"  <i class='fa fa-times-circle' aria-hidden='true'></i></label></li>";      
                    }
                }
            }
        } 
        await check_date();
        if(isWin || isMac){
            document.getElementById('show_select').innerHTML = text;
        }else if(isIPhone || isAndroid || isIPad){
            //mobile
            document.getElementById('show_select_mb').innerHTML = text;
            document.getElementById('show_select_all').innerHTML = text;
        }
    }
    window.onload = function() {
        setInitialLikedStatus();
    };
    // กด Favorite
    const likedTours = JSON.parse(localStorage.getItem('likedTours')) || [];

    function setInitialLikedStatus() {
        const heartIcons = document.querySelectorAll('.wishlist');
        
        heartIcons.forEach(icon => {
            const tourId = parseInt(icon.getAttribute('data-tour-id'));
            if (likedTours.includes(tourId)) {
                icon.classList.add('active');
            }
        });
    }

    async function likedTour(tourId) {
        const index = likedTours.indexOf(tourId);

        if (index === -1) {
            likedTours.push(tourId);
        } else {
            likedTours.splice(index, 1);
        }

        // บันทึก likedTours ใน local storage
        localStorage.setItem('likedTours', JSON.stringify(likedTours));

        // อัปเดตสถานะของไอคอนถูกใจ
        const heartIcon = document.querySelector(`[data-tour-id="${tourId}"]`);
        if (likedTours.includes(tourId)) {
            heartIcon.classList.add('active');
            toastr.success("ได้เพิ่มทัวร์ในรายการที่ต้องการสำเร็จแล้ว");
        } else {
            heartIcon.classList.remove('active');
            toastr.error("ลบรายการทัวร์ที่ต้องการสำเร็จแล้ว");
        }

        const likedCountElement = document.getElementById('numberwishls');
        // แสดงจำนวนที่ถูกใจใน header
        likedCountElement.textContent = `${likedTours.length}`;
    }
    // เรียงลำดับ
    async function OrderByData(value){
        count_pagin = 1;
        // ราคาถูกที่สุด
        if(value == 1){
           await data_tour.sort((a, b) => a.price - b.price);
                       main_tour = new Array();
            var count_tour = 0;
            for(let d in data_tour){
                if(!main_tour[count_tour]){
                    main_tour[count_tour] = new Array();
                }
                if(type_data.start_date.length){
                    var period_data = await period.filter(x=> x.tour_id == data_tour[d].id  && x.count > 0 && x.status_period != 3 && new Date(x.start_date).valueOf() >= new Date(type_data.start_date).valueOf()  && new Date(x.start_date).valueOf() <=   new Date(type_data.end_date).valueOf());
                    var period_soldout = await period.filter(x=> x.tour_id == data_tour[d].id && x.count == 0 && x.status_period == 3 && new Date(x.start_date).valueOf() >= new Date(type_data.start_date).valueOf()  && new Date(x.start_date).valueOf() <=   new Date(type_data.end_date).valueOf());
                }else{
                    var period_data = await period.filter(x=> x.tour_id == data_tour[d].id  && x.count > 0 && x.status_period != 3);
                    var period_soldout = await period.filter(x=> x.tour_id == data_tour[d].id && x.count == 0 && x.status_period == 3);
                }
                // let period_data = await period.filter(x=> x.tour_id == data_tour[d].id && x.count > 0 && x.status_period != 3);
                let group_period = new Array();
                    for(let p in period_data){
                        if(!group_period[period_data[p].group_date]){
                            group_period[period_data[p].group_date] = new Array;
                        }
                        group_period[period_data[p].group_date].push(period_data[p])
                    }
                let country_data = JSON.parse(data_tour[d].country_id);
                var country_fil = new Array();
                for(let t in country_data){
                    country_fil =  await country.filter(x => x.id == country_data[t]);
                }
                let info = {
                    tour:data_tour[d],
                    airline: await airline.find(x=> x.id == data_tour[d].airline_id),
                    period: group_period,
                    tour_type: await tour_type.find(x=>x.id == data_tour[d].type_id),
                    sold_out: period_soldout,
                    country:country_fil,
                }
                main_tour[count_tour].push(info);
                if(main_tour[count_tour].length >= 15){
                    count_tour++;
                }
            }
            document.getElementById('show_total').innerHTML = 'พบ '+data_tour.length+' รายการ';
            total_page = count_tour;
           
            await show_tour(0);
            // readMore();
            await SelectFilter();
        }
        // ดูมากสุด
        if(value == 2){
            await data_tour.sort((a, b) => b.tour_views - a.tour_views);
                         main_tour = new Array();
             var count_tour = 0;
             for(let d in data_tour){
                 if(!main_tour[count_tour]){
                     main_tour[count_tour] = new Array();
                 }
                 if(type_data.start_date.length){
                    var period_data = await period.filter(x=> x.tour_id == data_tour[d].id  && x.count > 0 && x.status_period != 3 && new Date(x.start_date).valueOf() >= new Date(type_data.start_date).valueOf()  && new Date(x.start_date).valueOf() <=   new Date(type_data.end_date).valueOf());
                    var period_soldout = await period.filter(x=> x.tour_id == data_tour[d].id && x.count == 0 && x.status_period == 3 && new Date(x.start_date).valueOf() >= new Date(type_data.start_date).valueOf()  && new Date(x.start_date).valueOf() <=   new Date(type_data.end_date).valueOf());
                }else{
                    var period_data = await period.filter(x=> x.tour_id == data_tour[d].id  && x.count > 0 && x.status_period != 3);
                    var period_soldout = await period.filter(x=> x.tour_id == data_tour[d].id && x.count == 0 && x.status_period == 3);
                }
                //  let period_data = await period.filter(x=> x.tour_id == data_tour[d].id && x.count > 0 && x.status_period != 3);
                 let group_period = new Array();
                 for(let p in period_data){
                     if(!group_period[period_data[p].group_date]){
                         group_period[period_data[p].group_date] = new Array;
                     }
                     group_period[period_data[p].group_date].push(period_data[p])
                 }
                 let country_data = JSON.parse(data_tour[d].country_id);
                 var country_fil = new Array();
                 for(let t in country_data){
                     country_fil =  await country.filter(x => x.id == country_data[t]);
                }
                let info = {
                    tour:data_tour[d],
                    airline: await airline.find(x=> x.id == data_tour[d].airline_id),
                    period: group_period,
                    tour_type: await tour_type.find(x=>x.id == data_tour[d].type_id),
                    sold_out: period_soldout,
                    country:country_fil,
                }
                main_tour[count_tour].push(info);
                if(main_tour[count_tour].length >= 15){
                     count_tour++;
                }
             }
             document.getElementById('show_total').innerHTML = 'พบ '+data_tour.length+' รายการ';
             total_page = count_tour;
             
             await show_tour(0);
            //  readMore();
            await SelectFilter();
        }
        // มีลดราคา
        if(value == 3){
            var order_tour = await data_tour.filter(x => special_price.includes(x.id));
                        main_tour = new Array();
            var count_tour = 0;
            var count_num = 0;
            for(let d in order_tour){
                if(order_tour[d].special_price > 0 ){
                    if(!main_tour[count_tour]){
                        main_tour[count_tour] = new Array();
                    }
                    if(type_data.start_date.length){
                        var period_data = await period.filter(x=> x.tour_id == data_tour[d].id  && x.count > 0 && x.status_period != 3 && new Date(x.start_date).valueOf() >= new Date(type_data.start_date).valueOf()  && new Date(x.start_date).valueOf() <=   new Date(type_data.end_date).valueOf());
                        var period_soldout = await period.filter(x=> x.tour_id == data_tour[d].id && x.count == 0 && x.status_period == 3 && new Date(x.start_date).valueOf() >= new Date(type_data.start_date).valueOf()  && new Date(x.start_date).valueOf() <=   new Date(type_data.end_date).valueOf());
                    }else{
                        var period_data = await period.filter(x=> x.tour_id == data_tour[d].id  && x.count > 0 && x.status_period != 3);
                        var period_soldout = await period.filter(x=> x.tour_id == data_tour[d].id && x.count == 0 && x.status_period == 3);
                    }
                    // let period_data = await period.filter(x=> x.tour_id == order_tour[d].id && x.count > 0 && x.status_period != 3);
                    let group_period = new Array();
                    for(let p in period_data){
                        if(!group_period[period_data[p].group_date]){
                            group_period[period_data[p].group_date] = new Array;
                        }
                        group_period[period_data[p].group_date].push(period_data[p])
                    }
                    let country_data = JSON.parse(order_tour[d].country_id);
                    var country_fil = new Array();
                    for(let t in country_data){
                         country_fil =  await country.filter(x => x.id == country_data[t]);
                    }
                    let info = {
                        tour:order_tour[d],
                        airline: await airline.find(x=> x.id == order_tour[d].airline_id),
                        period: group_period,
                        tour_type: await tour_type.find(x=>x.id == order_tour[d].type_id),
                        sold_out: period_soldout,
                        country:country_fil,
                    }
                    main_tour[count_tour].push(info);
                    if(main_tour[count_tour].length >= 15){
                        count_tour++;
                    }
                    count_num++;
                }
            }
            document.getElementById('show_total').innerHTML = 'พบ '+count_num+' รายการ';
            total_page = count_tour;
            await show_tour(0);
            // readMore();
            await SelectFilter();
        }
        // มีโปรโมชั่น
        if(value == 4){
            var order_tour = await data_tour.filter(x => promotion.includes(x.id));
                        main_tour = new Array();
            var count_tour = 0;
            var count_num = 0;
            for(let d in order_tour){
                if(order_tour[d].special_price > 0 ){
                    if(!main_tour[count_tour]){
                        main_tour[count_tour] = new Array();
                    }
                    if(type_data.start_date.length){
                        var period_data = await period.filter(x=> x.tour_id == data_tour[d].id  && x.count > 0 && x.status_period != 3 && new Date(x.start_date).valueOf() >= new Date(type_data.start_date).valueOf()  && new Date(x.start_date).valueOf() <=   new Date(type_data.end_date).valueOf());
                        var period_soldout = await period.filter(x=> x.tour_id == data_tour[d].id && x.count == 0 && x.status_period == 3 && new Date(x.start_date).valueOf() >= new Date(type_data.start_date).valueOf()  && new Date(x.start_date).valueOf() <=   new Date(type_data.end_date).valueOf());
                    }else{
                        var period_data = await period.filter(x=> x.tour_id == data_tour[d].id  && x.count > 0 && x.status_period != 3);
                        var period_soldout = await period.filter(x=> x.tour_id == data_tour[d].id && x.count == 0 && x.status_period == 3);
                    }
                    // let period_data = await period.filter(x=> x.tour_id == order_tour[d].id && x.count > 0 && x.status_period != 3);
                    let group_period = new Array();
                    for(let p in period_data){
                        if(!group_period[period_data[p].group_date]){
                            group_period[period_data[p].group_date] = new Array;
                        }
                        group_period[period_data[p].group_date].push(period_data[p])
                    }
                    let country_data = JSON.parse(order_tour[d].country_id);
                    var country_fil = new Array();
                    for(let t in country_data){
                         country_fil =  await country.filter(x => x.id == country_data[t]);
                    }
                    let info = {
                        tour:order_tour[d],
                        airline: await airline.find(x=> x.id == order_tour[d].airline_id),
                        period: group_period,
                        tour_type: await tour_type.find(x=>x.id == order_tour[d].type_id),
                        sold_out: period_soldout,
                        country:country_fil,
                    }
                    main_tour[count_tour].push(info);
                    if(main_tour[count_tour].length >= 15){
                        count_tour++;
                    }
                    count_num++;
                }
            }
            document.getElementById('show_total').innerHTML = 'พบ '+count_num+' รายการ';
            total_page = count_tour;
            await show_tour(0);
            // readMore();
            await SelectFilter();
       }
       // ไม่เรียง
       if(value == 0){
            // data_tour = await tour;
            main_tour = new Array();
            var count_tour = 0;
            for(let d in data_tour){
                if(!main_tour[count_tour]){
                    main_tour[count_tour] = new Array();
                }
                if(type_data.start_date.length){
                    var period_data = await period.filter(x=> x.tour_id == data_tour[d].id  && x.count > 0 && x.status_period != 3 && new Date(x.start_date).valueOf() >= new Date(type_data.start_date).valueOf()  && new Date(x.start_date).valueOf() <=   new Date(type_data.end_date).valueOf());
                    var period_soldout = await period.filter(x=> x.tour_id == data_tour[d].id && x.count == 0 && x.status_period == 3 && new Date(x.start_date).valueOf() >= new Date(type_data.start_date).valueOf()  && new Date(x.start_date).valueOf() <=   new Date(type_data.end_date).valueOf());
                }else{
                    var period_data = await period.filter(x=> x.tour_id == data_tour[d].id  && x.count > 0 && x.status_period != 3);
                    var period_soldout = await period.filter(x=> x.tour_id == data_tour[d].id && x.count == 0 && x.status_period == 3);
                }
                // let period_data = await period.filter(x=> x.tour_id == data_tour[d].id && x.count > 0 && x.status_period != 3);
                let group_period = new Array();
                for(let p in period_data){
                    if(!group_period[period_data[p].group_date]){
                        group_period[period_data[p].group_date] = new Array;
                    }
                    group_period[period_data[p].group_date].push(period_data[p])
                }
                let country_data = JSON.parse(data_tour[d].country_id);
                var country_fil = new Array();
                for(let t in country_data){
                     country_fil =  await country.filter(x => x.id == country_data[t]);
                }
                let info = {
                    tour:data_tour[d],
                    airline: await airline.find(x=> x.id == data_tour[d].airline_id),
                    period: group_period,
                    tour_type: await tour_type.find(x=>x.id == data_tour[d].type_id),
                    sold_out: period_soldout,
                    country:country_fil,
                }
                main_tour[count_tour].push(info);
                if(main_tour[count_tour].length >= 15){
                    count_tour++;
                }
            }
            document.getElementById('show_total').innerHTML = 'พบ '+data_tour.length+' รายการ';
            total_page = count_tour;
            await show_tour(0);
            // readMore();
            await SelectFilter();
       }
    }
     // เรียงหาประเเภททัวร์
     async function OrderByType(value){
            var order_tour = await data_tour.filter(x => x.type_id == value);
            main_tour = new Array();
            var count_tour = 0;
            var count_num = 0;
            count_pagin = 1;
            for(let d in order_tour){
                    if(!main_tour[count_tour]){
                        main_tour[count_tour] = new Array();
                    }
                    if(type_data.start_date.length){
                        var period_data = await period.filter(x=> x.tour_id == data_tour[d].id  && x.count > 0 && x.status_period != 3 && new Date(x.start_date).valueOf() >= new Date(type_data.start_date).valueOf()  && new Date(x.start_date).valueOf() <=   new Date(type_data.end_date).valueOf());
                        var period_soldout = await period.filter(x=> x.tour_id == data_tour[d].id && x.count == 0 && x.status_period == 3 && new Date(x.start_date).valueOf() >= new Date(type_data.start_date).valueOf()  && new Date(x.start_date).valueOf() <=   new Date(type_data.end_date).valueOf());
                    }else{
                        var period_data = await period.filter(x=> x.tour_id == data_tour[d].id  && x.count > 0 && x.status_period != 3);
                        var period_soldout = await period.filter(x=> x.tour_id == data_tour[d].id && x.count == 0 && x.status_period == 3);
                    }
                    // let period_data = await period.filter(x=> x.tour_id == order_tour[d].id && x.count > 0 && x.status_period != 3);
                    let group_period = new Array();
                    for(let p in period_data){
                        if(!group_period[period_data[p].group_date]){
                            group_period[period_data[p].group_date] = new Array;
                        }
                        group_period[period_data[p].group_date].push(period_data[p])
                    }
                    let country_data = JSON.parse(order_tour[d].country_id);
                    var country_fil = new Array();
                    for(let t in country_data){
                         country_fil =  await country.filter(x => x.id == country_data[t]);
                    }
                    let info = {
                        tour:order_tour[d],
                        airline: await airline.find(x=> x.id == order_tour[d].airline_id),
                        period: group_period,
                        tour_type: await tour_type.find(x=>x.id == order_tour[d].type_id),
                        sold_out: period_soldout,
                        country:country_fil,
                    }
                    main_tour[count_tour].push(info);
                    if(main_tour[count_tour].length >= 15){
                        count_tour++;
                    }
                    count_num++;
                
            }
            document.getElementById('show_total').innerHTML = 'พบ '+count_num+' รายการ';
            total_page = count_tour;
            await show_tour(0);
            // readMore();
            await SelectFilter();
       
    }
    // Datepicker

    $(function() {
        $('input[name="daterange"]').daterangepicker({
            opens: 'left',
            minDate: min_date,
        }, function(start, end, label) {
            document.getElementById('s_date').value = start.format('YYYY-MM-DD');
            document.getElementById('e_date').value = end.format('YYYY-MM-DD');
            document.getElementById('s_date_mb').value = start.format('YYYY-MM-DD');
            document.getElementById('e_date_mb').value = end.format('YYYY-MM-DD');
            
             var start_value = start.format('YYYY-MM-DD');
             var end_value = end.format('YYYY-MM-DD');
             if(start && end){
                type_data.start_date = new Array();
                type_data.end_date = new Array();
                 type_data.start_date.push(start_value)
                 type_data.end_date.push(end_value)
                 count_pagin = 1;
                 filter_tour();
                 check_date();
            }
            let y = new Date(start);
            let x = new Date(end);
            let s_show = y.getDate()+'  '+months[y.getMonth()]+'  '+(y.getFullYear()*1+543);
            let e_show = x.getDate()+'  '+months[x.getMonth()]+'  '+(x.getFullYear()*1+543);
            var s_select = y.getDate()+'/'+month_number[y.getMonth()]+'/'+(y.getFullYear()*1+543);
            let e_select = x.getDate()+'/'+month_number[x.getMonth()]+'/'+(x.getFullYear()*1+543);
            let s_day = days[y.getDay()];
            let e_day = days[x.getDay()];
 
            var text_start = '';
                text_start += "<span style='font-size:0.8rem;padding:3px 2px;display:block;'>"+s_show+"</span>";
                text_start += "<span style='font-size:0.8rem;padding:3px 2px;display:block;'>"+s_day+"</span>";
            var text_end = '';
                text_end += "<span style='font-size:0.8rem;padding:3px 2px;display:block;'>"+e_show+"</span>";
                text_end += "<span style='font-size:0.8rem;padding:3px 2px;display:block;'>"+e_day+"</span>";
 
             if(isWin || isMac){
                 document.getElementById('show_select_date').innerHTML = "<li onclick='DeletedDate()'><label class='check-container'>"+s_select+" ถึง "+e_select+"<i class='fa fa-times-circle' aria-hidden='true'></i></label></li>";
                 document.getElementById('show_date_calen').innerHTML = text_start;
                 document.getElementById('show_end_calen').innerHTML = text_end;
             }else if(isAndroid || isIPhone || isIPad){
                 //mobile
                 document.getElementById('show_select_date_mb').innerHTML = "<li onclick='DeletedDate()'><label class='check-container'>"+s_select+" ถึง "+e_select+"<i class='fa fa-times-circle' aria-hidden='true'></i></label></li>";  
                 document.getElementById('show_select_date_all').innerHTML = "<li onclick='DeletedDate()'><label class='check-container'>"+s_select+" ถึง "+e_select+"<i class='fa fa-times-circle' aria-hidden='true'></i></label></li>";  
                 document.getElementById('show_date_calen_mb').innerHTML = text_start;
                 document.getElementById('show_end_calen_mb').innerHTML = text_end;
             }
            $('#show_date_calen').show();
            $('#show_end_calen').show();
            $('#hide_date_select').hide();
            //mobile
            $('#show_date_calen_mb').show();
            $('#show_end_calen_mb').show();
            $('#hide_date_select_mb').hide();
        });
        $('input[name="daterange"]').on('cancel.daterangepicker', function(ev, picker) {
            document.getElementById('s_date').value = null;
            document.getElementById('e_date').value = null;
            document.getElementById('show_select_date').innerHTML = '';
            //mobile
            document.getElementById('s_date_mb').value = null;
            document.getElementById('e_date_mb').value = null;
            document.getElementById('show_select_date_mb').innerHTML = '';
            document.getElementById('show_select_date_all').innerHTML = '';
           
            let y = new Date();
            let x = new Date(y.valueOf()+86400000);
            
            let s_show = y.getDate()+'  '+months[y.getMonth()]+'  '+(y.getFullYear()*1+543);
            let e_show = x.getDate()+'  '+months[x.getMonth()]+'  '+(x.getFullYear()*1+543);
            let s_day = days[y.getDay()];
            let e_day = days[x.getDay()];
            var dateS_now = (month_number[y.getMonth()])+'/'+y.getDate()+'/'+y.getFullYear()*1;
            var dateE_now = (month_number[x.getMonth()])+'/'+x.getDate()+'/'+x.getFullYear()*1;
            
            var text_start1 = '';
                text_start1 += "<span style='font-size:0.8rem;padding:3px 2px;display:block;color:gray;'>"+s_show+"</span>";
                text_start1 += "<span style='font-size:0.8rem;padding:3px 2px;display:block;color:gray;'>"+s_day+"</span>";
            var text_end2 = '';
                text_end2 += "<span style='font-size:0.8rem;padding:3px 2px;display:block;color:gray;'>"+e_show+"</span>";
                text_end2 += "<span style='font-size:0.8rem;padding:3px 2px;display:block;color:gray;'>"+e_day+"</span>";
 
                if(isWin || isMac){
                     document.getElementById('show_date_calen').innerHTML = text_start1;
                     document.getElementById('show_end_calen').innerHTML = text_end2;
                     document.getElementById('hide_date_select').value = dateS_now+" - "+dateE_now;
                }else if(isAndroid || isIPhone || isIPad){
                     document.getElementById('show_date_calen_mb').innerHTML = text_start1;
                     document.getElementById('show_end_calen_mb').innerHTML = text_end2;
                     document.getElementById('hide_date_select_mb').value = dateS_now+" - "+dateE_now;
                }
 
            $('#show_date_calen').show();
            $('#show_end_calen').show();
            $('#hide_date_select').hide();
             //mobile
            $('#show_date_calen_mb').show();
            $('#show_end_calen_mb').show();
            $('#hide_date_select_mb').hide();
 
            //ลบค่า tour
            var index_start = type_data['start_date'].indexOf(0);
            type_data['start_date'].splice(index_start,1);
            var index_end = type_data['end_date'].indexOf(0);
            type_data['end_date'].splice(index_end,1);
            count_pagin = 1;
            filter_tour();
 
        });
        $('input[name="daterange"]').on('hide.daterangepicker', function(ev, picker) {
            $('#show_date_calen').show();
            $('#show_end_calen').show();
            $('#hide_date_select').hide();
             //mobile
            $('#show_date_calen_mb').show();
            $('#show_end_calen_mb').show();
            $('#hide_date_select_mb').hide();
 
        });
    });
    async  function show_datepicker() {
       $('#show_date_calen').hide();
       $('#show_end_calen').hide();
       $('#hide_date_select').show();
       document.getElementById("hide_date_select").click();
   }
    async function show_datepicker_mb() {
        $('#show_date_calen_mb').hide();
        $('#show_end_calen_mb').hide();
        $('#hide_date_select_mb').show();
        document.getElementById("hide_date_select_mb").click();
    }
    async function DeletedDate(){
        document.getElementById('show_select_date').innerHTML = '';
        //mobile
        document.getElementById('show_select_date_mb').innerHTML = '';
        document.getElementById('show_select_date_all').innerHTML = '';
        document.getElementById('s_date').value = null;
        document.getElementById('e_date').value = null;
        document.getElementById('s_date_mb').value = null;
        document.getElementById('e_date_mb').value = null;
       let y = new Date();
       let x = new Date(y.valueOf()+86400000);
       var dateS_now = (month_number[y.getMonth()])+'/'+y.getDate()+'/'+y.getFullYear()*1;
       var dateE_now = (month_number[x.getMonth()])+'/'+x.getDate()+'/'+x.getFullYear()*1;
       let s_show = y.getDate()+'  '+months[y.getMonth()]+'  '+(y.getFullYear()*1+543);
       let e_show = x.getDate()+'  '+months[x.getMonth()]+'  '+(x.getFullYear()*1+543);
       let s_day = days[y.getDay()];
       let e_day = days[x.getDay()];
       
       var text_start1 = '';
           text_start1 += "<span style='font-size:0.8rem;padding:3px 2px;display:block;color:gray;'>"+s_show+"</span>";
           text_start1 += "<span style='font-size:0.8rem;padding:3px 2px;display:block;color:gray;'>"+s_day+"</span>";
       var text_end2 = '';
           text_end2 += "<span style='font-size:0.8rem;padding:3px 2px;display:block;color:gray;'>"+e_show+"</span>";
           text_end2 += "<span style='font-size:0.8rem;padding:3px 2px;display:block;color:gray;'>"+e_day+"</span>";
       

        if(isWin || isMac){
            document.getElementById('hide_date_select').value = dateS_now+' - '+dateE_now;
            document.getElementById('show_date_calen').innerHTML = text_start1;
            document.getElementById('show_end_calen').innerHTML = text_end2;
        }else if(isAndroid || isIPhone || isIPad){
            document.getElementById('hide_date_select_mb').value = dateS_now+' - '+dateE_now;
            document.getElementById('show_date_calen_mb').innerHTML = text_start1;
            document.getElementById('show_end_calen_mb').innerHTML = text_end2;
        }

        // ลบค่าtour
        var index_start = type_data['start_date'].indexOf(0);
        type_data['start_date'].splice(index_start,1);
        var index_end = type_data['end_date'].indexOf(0);
        type_data['end_date'].splice(index_end,1);

        //ลบค่าวันที่ค้นหา
        start_search = 0;
        end_search = 0;
        count_pagin = 1;
        await filter_tour();
        await check_date();
    }
    async function check_date(){
        // console.log(type_data['start_date'],'check_date')
        if(type_data['start_date'].length){
            $('#hide_month').hide();
            $('#hide_month_mb').hide();
            $('#hide_holiday').hide();
            $('#hide_holiday_mb').hide();
        }else if(!type_data['start_date'].length && !type_data['month'].length && !type_data['holiday'].length){
            $('#hide_month').show();
            $('#hide_month_mb').show();
            $('#hide_holiday').show();
            $('#hide_holiday_mb').show();
        }
    }
    async function check_month(value){
        if(value.length){
            $('#hide_date').hide();
            $('#hide_date_mb').hide();
            $('#hide_holiday').hide();
            $('#hide_holiday_mb').hide();
            // console.log(value,'check_month')
        }else{
            $('#hide_date').show();
            $('#hide_date_mb').show();
            $('#hide_holiday').show();
            $('#hide_holiday_mb').show();
        }
    }
    async function check_holiday(value){
        // console.log(value,'check_holiday')
        if(value.length){
            $('#hide_date').hide();
            $('#hide_date_mb').hide();
            $('#hide_month').hide();
            $('#hide_month_mb').hide();
        }else{
            $('#hide_date').show();
            $('#hide_date_mb').show();
            $('#hide_month').show();
            $('#hide_month_mb').show();
        }
    }
check_date();
async function clear_filter(){
    type_data = {
        country: new Array(),
        price: new Array(),
        airline: new Array(),
        rating: new Array(),
        day:new Array(),
        month:new Array(),
        holiday:new Array(),
        city:new Array(),
        amupur:new Array(),
        start_date:new Array(),
        end_date:new Array(),
        travel_search:new Array(),
        tour_code:new Array(),
    }
    travel_search = 0;
    keyword_search = 0;
    tour_code = 0;
    code_id = 0;
    start_search = 0;
    end_search = 0;
    price_search = 0;
    country_search = 0;
    city_search = 0;
    count_pagin = 1;
    //keyword
    document.getElementById('show_keyword').innerHTML = '';
    document.getElementById('show_keyword_mb').innerHTML = '';
    document.getElementById('show_keyword_all').innerHTML = '';
    //code
    document.getElementById('show_code').innerHTML = '';
    document.getElementById('show_code_mb').innerHTML = '';
    document.getElementById('show_code_all').innerHTML = '';
    //select date
    document.getElementById('show_select_date').innerHTML = '';
    document.getElementById('show_select_date_mb').innerHTML = '';
    document.getElementById('show_select_date_all').innerHTML = '';
    document.getElementById('s_date').value = null;
    document.getElementById('e_date').value = null;
    document.getElementById('s_date_mb').value = null;
    document.getElementById('e_date_mb').value = null;
    let y = new Date();
    let x = new Date(y.valueOf()+86400000);
    var dateS_now = (month_number[y.getMonth()])+'/'+y.getDate()+'/'+y.getFullYear()*1;
    var dateE_now = (month_number[x.getMonth()])+'/'+x.getDate()+'/'+x.getFullYear()*1;
    if(isWin || isMac){
        document.getElementById('hide_date_select').value = dateS_now+' - '+dateE_now;
    }else if(isAndroid || isIPhone || isIPad){
        document.getElementById('hide_date_select_mb').value = dateS_now+' - '+dateE_now;
    }
    await check_date();
    await check_month(type_data.month);
    await check_holiday(type_data.holiday);
    if(!inthai_id){
        await show_country();
        $('#city-topic').hide();
        $('#amupur-topic').hide();
    }
    if(inthai_id){
        await show_amupur(false);
        $('#country-topic').hide();
        $('#city-topic').hide();
    }
    await show_price();
    await show_airline();
    await show_rating();
    await show_day();
    await show_month();
    await show_holiday();
    await date_picker();
    await filter_tour();
}
async function readMore(){
    var $readMore = "ดูช่วงเวลาเพิ่มเติม ";
    var $readLess = "ย่อข้อความ";
    $(".readMoreBtn").text($readMore);
    $('.readMoreBtn').click(function () {
        var $this = $(this);
        $this.text($readMore);
        if ($this.data('expanded') == "yes") {
            $this.data('expanded', "no");
            $this.text($readMore);
            $this.parent().find('.readMoreText').animate({
                maxHeight: '120px'
            });
        } else {
            $this.data('expanded', "yes");
            $this.parent().find('.readMoreText').css({
                maxHeight: 'none'
            });
            $this.text($readLess);

        }
    });

    var $readMore2 = "<i class=\"fi fi-rr-angle-small-down\"></i>";
    var $readLess2 = "<i class=\"fi fi-rr-angle-small-up\"></i>";
    $(".readMoreBtn2").html($readMore2);
    $('.readMoreBtn2').click(function () {
        var $this = $(this);
        $this.html($readMore2);
        if ($this.data('expanded') == "yes") {
            $this.data('expanded', "no");
            $this.html($readMore2);
            $this.parent().find('.readMoreText2').animate({
                height: '50px'
            });
        } else {
            $this.data('expanded', "yes");
            $this.parent().find('.readMoreText2').css({
                height: 'auto'
            });
            $this.html($readLess2);

        }
    });
}
   

