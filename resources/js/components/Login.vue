<template>
    <div class="min-h-screen flex items-center justify-center">
        <div class="bg-gray-800 p-8 rounded-lg shadow-xl w-full max-w-md">
            <h2 class="text-3xl font-bold text-center mb-200 text-center mb-8">VirgoSoft Exchange</h2>
        
            <form @submit.prevent="login">
                <input
                v-model="email"
                type="email"
                placeholder="Email"
                class="w-full p-3 mb-4 bg-gray-700 rounded border border-gray-600 focus:outline-none focus:border-blue-500"
                required
                />
                <input
                v-model="password"
                type="password"
                placeholder="Password"
                class="w-full p-3 mb-6 bg-gray-700 rounded border border-gray-600 focus:outline-none focus:border-blue-500"
                required
                />
                <button
                type="submit"
                class="w-full bg-blue-600 hover:bg-blue-700 py-3 rounded font-semibold transition"
                >
                Login
                </button>
            </form>

            <p class="text-center mt-6 text-gray-400">
                Test accounts:<br>
                alice@test.com / password<br>
                bob@test.com / password
            </p>
        </div>
    </div>
</template>

<script setup>
import { ref } from 'vue'
import { useRouter } from 'vue-router'
import axios from 'axios'

const email = ref('alice@test.com')
const password = ref('password')
const router = useRouter()

const login = async () => {
    try {
        const res = await axios.post('/api/login', {
            email: email.value,
            password: password.value
        })
        
        localStorage.setItem('token', res.data.token)
        axios.defaults.headers.common['Authorization'] = `Bearer ${res.data.token}`
        
        router.push('/dashboard')
    } catch (err) {
    alert('Login failed')
    }
}
</script>