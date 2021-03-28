<template>
  <div>
    <h1>Pusher Test</h1>
    <p>
      Publish an event to channel <code>my-channel</code>
      with event name <code>my-event</code>; it will appear below:
    </p>
    <div id="app">
      <ul>
        <li v-for="message in messages" :key="message">
          {{message}}
        </li>
      </ul>
    </div>
  </div>
</template>

<style>
</style>
<script>
// Enable pusher logging - don't include this in production
import Pusher from 'pusher-js';


export default {
  data () {
    return {
      messages: []
    }
  },
  created() {
    this.implement()
  },
  methods: {
    implement () {

      Pusher.logToConsole = true;

      let pusher = new Pusher('23bf5cf1702a1b1b0d49', {
        cluster: 'ap1'
      });

      let channel = pusher.subscribe('my-channel');
      channel.bind('my-event', function(data) {
        this.messages.push(JSON.stringify(data));
      });
    }
  },

}


// Vue application
</script>
