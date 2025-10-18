<script>
      function TagValue(){
                if(document.getElementById('browser').value != ''){
                    let search_tag = data.find(x => x.tag == document.getElementById('browser').value);
                    
                    if(search_tag){
                        arr.push(search_tag);
                        SaveTag();
                    }else{
                       
                        $.ajax({
                            type: 'POST',
                            url: '{{url("webpanel/travel/get-tag")}}',
                            data: {
                            _token: '{{csrf_token()}}',
                            tag: document.getElementById('browser').value,
                            },
                                success: function (data) {
                                arr.push(data);
                                SaveTag();
                            }
                        });
                    }
                  
                }
            }
            function SaveTag(){
                var a = '';
                var b = '';
                for(x=0;x < arr.length;x++){
                    a = a+'<button type="button" class="btn btn-outline-dark mr-2"> '+arr[x].tag+' &nbsp;<i onclick="DeleteTag('+arr[x].id+')"  class="fa fa-times fa-lg "></i> </button>';
                    b = b+'<input type="hidden"  class="abc" name="tag_id[]" value="'+arr[x].id+'"> ';
                }
                document.getElementById('hashtag').innerHTML = a+b;
                document.getElementById('browser').value = '';
                NewList();
                console.log(arr)
            }
            function DeleteTag(id){
                arr = arr.filter(x=>x.id != id);
                SaveTag();
            }
            function NewList(){
                var check = '';
                for(i=0;i<data.length;i++){
                    if(!arr.find(x=>x.id == data[i].id)){
                        check = check+ '<option >'+data[i].tag+'</option>' ;
                    }
                }
                document.getElementById('browsers').innerHTML = check;
            }
</script>