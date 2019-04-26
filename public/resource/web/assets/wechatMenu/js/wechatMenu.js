new Vue({
    el:'#box',
    data:{
        rightTitle:'',
        url:'',
        type:'',
        dataIndex:[0,0,null],
        operationShow: true,
        urlShow: true,
        isRightTitle: false,
        isUrl: false,
        menuIndex:[
            {isActive:false},
            {isActive:false},
            {isActive:false}
        ],
        subBtnIndex: [
            {num:-1},
            {num:-1},
            {num:-1}
        ],
        menuObj:{
            "button": data ? data : [],
        },
        buttonObj:{
            "type": "view",
            "name": "新建菜单",
            "url": "",
            "sub_button": []
        },
        subObj:{
            "type": "view",
            "name": "新建子菜单",
            "url": ""
        }
    },
    computed:{
        buttonWidth: function(){
            return this.menuObj.button.length==3?'33.33%':100/(this.menuObj.button.length+1)+'%';
        }
    },
    methods:{
        del: function(){
            let x=this.dataIndex[0];
            let y=this.dataIndex[1];
            let z=this.dataIndex[2];
            z==0?this.menuObj.button.splice(x,1):this.menuObj.button[x].sub_button.splice(y,1);//判断删除一级菜单还是二级菜单
            for(let i=0;i<this.menuObj.button.length;i++){
                this.subBtnIndex[i].num=-1;//重置所有子菜单选中状态
                this.menuIndex[i].isActive=false;//重置一级菜单
            }
            this.operationShow=true;//右侧隐藏操作框
        },
        addBtn: function(index){//添加一级菜单
            var button=this.menuObj.button;
            button.splice(button.length,0,JSON.parse(JSON.stringify(this.buttonObj)));//添加菜单按钮
            for(let i=0;i<this.menuObj.button.length;i++){
                this.subBtnIndex[i].num=-1;//重置所有子菜单选中状态
                this.menuIndex[i].isActive=false;//重置一级菜单
            }
            this.menuIndex[index].isActive=true;
            let menu_name=this.menuObj.button[index].name;
            let menu_url=this.menuObj.button[index].url;
            let menu_type=this.menuObj.button[index].type;
            this.getInfo(menu_name,menu_url,menu_type);
            this.urlWindow(index);

            this.dataIndex.splice(0,1,index);
            this.dataIndex.splice(2,1,0);
            this.operationShow=false;
        },
        subAddBtn: function(index){//添加子菜单
            for(let i=0;i<this.menuObj.button.length;i++){
                this.subBtnIndex[i].num=-1;//重置所有子菜单选中状态
                this.menuIndex[i].isActive=false;//重置一级菜单
                if(i ==index){
                    var sub_button=this.menuObj.button[i].sub_button;
                    sub_button.splice(this.menuObj.button[i].sub_button.length,0,JSON.parse(JSON.stringify(this.subObj)));//添加子菜单按钮
                    if(sub_button.length>0){
                        this.subBtnIndex[index].num=sub_button.length-2;
                        this.menuObj.button[i].url='';//增加子菜单，清空一级菜单url
                        if(this.menuObj.button[i].type) delete(this.menuObj.button[i].type);// 增加子菜单，清空一级菜单type
                    }
                    this.subBtnIndex[index].num++;//子菜单选中状态
                }
            }
            let sub_name=this.menuObj.button[index].sub_button[this.subBtnIndex[index].num].name;
            let sub_url=this.menuObj.button[index].sub_button[this.subBtnIndex[index].num].url;
            let sub_type=this.menuObj.button[index].sub_button[this.subBtnIndex[index].num].type;
            this.getInfo(sub_name,sub_url,sub_type);
            this.urlWindow(index,true);

            this.dataIndex.splice(0,1,index);
            this.dataIndex.splice(1,1,this.subBtnIndex[index].num);
            this.dataIndex.splice(2,1,1);
            this.operationShow=false;
        },
        menuTab: function(index){//点击一级菜单
            for(let i=0;i<this.menuObj.button.length;i++){
                this.subBtnIndex[i].num=-1;//重置所有子菜单选中状态
                this.menuIndex[i].isActive=false;//重置一级菜单
            }
            this.menuIndex[index].isActive=true;
            let menu_name=this.menuObj.button[index].name;
            let menu_url=this.menuObj.button[index].url;
            let menu_type=this.menuObj.button[index].type;
            this.getInfo(menu_name,menu_url,menu_type);
            this.urlWindow(index);

            this.dataIndex.splice(0,1,index);
            this.dataIndex.splice(2,1,0);
            this.operationShow=false;
        },
        liTab: function(index,subIndex){//点击子菜单
            for(let i=0;i<this.menuObj.button.length;i++){
                this.subBtnIndex[i].num=-1;//重置所有子菜单选中状态
                this.menuIndex[i].isActive=false;//重置一级菜单
            }
            this.subBtnIndex[index].num=subIndex;
            let sub_name=this.menuObj.button[index].sub_button[subIndex].name;
            let sub_url=this.menuObj.button[index].sub_button[subIndex].url;
            let sub_type = this.menuObj.button[index].sub_button[subIndex].type;
            this.getInfo(sub_name,sub_url,sub_type);
            this.urlWindow(index,true);

            this.dataIndex.splice(0,1,index);
            this.dataIndex.splice(1,1,subIndex);
            this.dataIndex.splice(2,1,1);
            this.operationShow=false;
        },
        updateValue: function(){//更新按钮name
            let match1=/^[\u4E00-\u9FA5]{1,5}$/;
            let isName1=match1.test(this.rightTitle);
            let match2=/^[a-zA-Z0-9]{1,16}$/;
            let isName2=match2.test(this.rightTitle);

            let x=this.dataIndex[0];
            let y=this.dataIndex[1];
            let z=this.dataIndex[2];
            isName1 || isName2?this.isRightTitle=false:this.isRightTitle=true;
            z==0?this.setmenu(x):this.setSubmenu(x,y);
        },
        updateUrl: function(){//更新按钮url
            let match = /^((ht|f)tps?):\/\/[\w\-]+(\.[\w\-]+)+([\w\-\.,@?^=%&:\/~\+#]*[\w\-\@?^=%&\/~\+#])?$/;//验证url
            let isUrl = match.test(this.url);
            let x=this.dataIndex[0];
            let y=this.dataIndex[1];
            let z=this.dataIndex[2];
            isUrl?this.isUrl=false:this.isUrl=true;
            z==0?this.setmenu(x):this.setSubmenu(x,y);
        },
        getInfo: function(name,url,type){//显示name，url
            this.rightTitle=name;
            this.url=url;
            if(type) {
                this.type = type;
            }else{
                this.type = '';
            }
        },
        setmenu: function(x){
            this.menuObj.button[x].name=JSON.parse(JSON.stringify(this.rightTitle));//必须用JSON.parse(JSON.stringify(value)
            this.menuObj.button[x].url=JSON.parse(JSON.stringify(this.url));
        },
        setSubmenu: function(x,y){
            this.menuObj.button[x].sub_button[y].name=JSON.parse(JSON.stringify(this.rightTitle));
            this.menuObj.button[x].sub_button[y].url=JSON.parse(JSON.stringify(this.url));
        },
        urlWindow: function(index,subIndex){//显示右侧url窗口
            if(!subIndex && this.menuObj.button[index].sub_button.length>0){
                this.urlShow = false;
            }else{
                this.urlShow = true;
            }
        },
        save: function(){
            let button=this.menuObj.button;
            // console.log(button);
            // return false;
            for(let i=0;i<button.length;i++){//验证url是否为空
                if(button[i].sub_button.length==0){
                    if(button[i].url=='' || button[i].name==''){
                        Swal.fire({
                            title: '一级菜单内容不能为空',
                            type:'error',
                        });
                        return;
                    }
                }else{
                    for(let j=0;j<button[i].sub_button.length;j++){
                        if(button[i].sub_button[j].url=='' || button[i].sub_button[j].name==''){
                            Swal.fire({
                                title: '二级菜单内容不能为空',
                                type:'error',
                            });
                            return;
                        }
                    }
                }
            }
            //ajax保存至后台
            // console.log(this.menuObj.button);
            $.post('/system/conf/wechat_menu.html',{conf_content:this.menuObj.button},function (data) {
                console.log(data);
                if(data.state == 200){
                    Swal.fire({
                        title: data.msg,
                        type:'success',
                        timer: 2000,
                        onClose: () => {
                            window.location.reload();
                        }
                    });
                }else{
                    Swal.fire({
                        title: data.msg,
                        type:'error',
                        timer: 2000,
                    });
                }
            },'json')
        }
    }
})
