<template>
    <div id="main">
        <header id="header">
            <Menu></Menu>
        </header>
        <div id="content">
            <router-view></router-view>
            <center><small>AltFantasySports is an open source project. Contribute at <a target="_blank" href="https://github.com/bwentzloff/afs">https://github.com/bwentzloff/afs</a></small></center>
        </div>
    </div>
</template>
<script>
  import Menu from './components/Menu.vue'
  export default {
    data() {
      return {
        //
      }
    },
    components: {
      Menu
    }
  }
</script>