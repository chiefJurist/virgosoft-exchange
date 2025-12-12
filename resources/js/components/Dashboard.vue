<template>
    <div class="p-6">
        <div class="flex justify-between items-center mb-8">
            <h1 class="text-3xl font-bold">Dashboard</h1>
            <button @click="logout" class="bg-red-600 hover:bg-red-700 px-6 py-2 rounded">
                Logout
            </button>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
            <div class="bg-gray-800 p-6 rounded-lg">
                <h3 class="text-xl mb-2">USD Balance</h3>
                <p class="text-3xl font-bold text-green-400">${{ profile.balance?.toFixed(2) }}</p>
            </div>
            <div class="bg-gray-800 p-6 rounded-lg">
                <h3 class="text-xl mb-2">BTC</h3>
                <p class="text-3xl font-bold text-orange-400">
                {{ (profile.assets?.BTC?.available || 0).toFixed(8) }}
                </p>
            </div>
            <div class="bg-gray-800 p-6 rounded-lg">
                <h3 class="text-xl mb-2">ETH</h3>
                <p class="text-3xl font-bold text-purple-400">
                {{ (profile.assets?.ETH?.available || 0).toFixed(8) }}
                </p>
            </div>
        </div>

        <div class="text-center text-gray-400">
            <p>Order placement & orderbook coming in next step</p>
            <p class="mt-4">Real-time updates already working!</p>
        </div>
    </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import axios from 'axios'

const profile = ref({})
const router = useRouter()

onMounted(async () => {
    try {
        const res = await axios.get('/api/profile')
        profile.value = res.data
    } catch (err) {
        router.push('/')
    }
})

const logout = () => {
    localStorage.removeItem('token')
    delete axios.defaults.headers.common['Authorization']
    router.push('/')
}
</script>