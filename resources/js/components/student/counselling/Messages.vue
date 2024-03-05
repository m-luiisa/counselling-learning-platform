<template>
    <div>
        <message v-for="(message, index) in messages" :key="index" :data="message" :persona="counselling.persona" :pseudonym="pseudonym" :courseFinished="courseFinished"></message>
    </div>
</template>
<script>
export default {
    props: ['counselling', 'messages', 'pseudonym', 'courseFinished'],
}
</script>
<style lang="">
    
</style>