### 1. keepAlive + vue-router 实现记录位置
index.js
```
const tagDetail = [{
  path: "/tagDetail/:name",
  meta: {
    keepAlive: true, // 开启keepalive
    isBack: false    // 判断要不要使用缓存数据
  },
  component: resolve => require(["@/page/tagDetail"], resolve)
}];
```
app.vue
```
<keep-alive>
    <router-view v-if="$route.meta.keepAlive" class="appView"></router-view>
</keep-alive>
<router-view v-if="!$route.meta.keepAlive" class="appView"></router-view>
```
tagDetail.vue
```
keep-alive 组件停用时调用 记录当前滚动位置
beforeDestroy(){
  console.log(this.$refs.squareBox.scrollTop);
  localStorage.setItem("tagDetailPosition", this.$refs.squareBox.scrollTop);
},
```
```
keep-alive 组件激活时调用 判断如果是isback = true 就请求数据 否则 使用缓存并设置记录的位置
activated() {
  if (!this.$route.meta.isBack) {
    console.log("~~isBack~~");
    this.list = [];
    this.page = 1;
    this.isCeil = true;
    this.tagMomentList();
  } else {
    console.log("~~noisBack~~");
    console.log(localStorage.getItem("tagDetailPosition"));
    this.$refs.squareBox.scrollTop = localStorage.getItem("tagDetailPosition");
  }
  this.$route.meta.isBack = false;
},
```
```
路由加载前
beforeRouteEnter(to,from,next){
  console.log(to,from); // 如果不是从详情页过来的 isback = true 否则等于 false
  if (from.path.indexOf("squareDetails") != -1) {
    to.meta.isBack = true;
  } else {
    to.meta.isBack = false;
  }
  next();
}
```
***
