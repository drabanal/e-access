<script setup lang="ts">
import { onMounted, ref } from 'vue';
import PlaceholderPattern from '../../components/PlaceholderPattern.vue';
import axios from 'axios';


const fetchingCredits = ref(true);
const leaveCredits = ref([])

const props = defineProps({ memberCredits: Array, employee: Object })

const fetchLeaveCredits = async () => {
    fetchingCredits.value = true
    await axios.get('/leaves/credits').then((response) => {
        leaveCredits.value = response.data
        fetchingCredits.value = false
    })
    .catch((error) => {
        console.log(error)
    });
}

onMounted(() => {
    fetchingCredits.value = false
    if (!props.memberCredits) {
        fetchLeaveCredits()
    } else {
        leaveCredits.value = props.memberCredits
    }
})
</script>

<template>
    <div class="grid auto-rows-min mb-5">
        <div class="relative overflow-hidden ">
            <PlaceholderPattern v-if="fetchingCredits" />
            <div class="grid grid-cols-6 gap-3 mt-2 uppercase" v-else>
                <div class="bg-green-500 p-4 rounded-2xl text-white">
                    <p class="text-center mb-1 text-base">
                        <strong>Vacation</strong>
                    </p>
                    <p>
                        <strong>Available</strong> - {{ leaveCredits[1].available }}
                    </p>
                    <p>
                        <strong>Used</strong> - {{ leaveCredits[1].used }}
                    </p>
                    <p>
                        <strong>Remaining</strong> - {{ leaveCredits[1].remaining }}
                    </p>
                </div>
                <div class="bg-red-500 p-4 rounded-2xl text-white">
                    <p class="text-center mb-1 text-base">
                        <strong>Sick</strong>
                    </p>
                    <p>
                        <strong>Available</strong> - {{ leaveCredits[0].available }}
                    </p>
                    <p>
                        <strong>Used</strong> - {{ leaveCredits[0].used }}
                    </p>
                    <p>
                        <strong>Remaining</strong> - {{ leaveCredits[0].remaining }}
                    </p>
                </div>
                <div class="bg-orange-500 p-4 rounded-2xl text-white">
                    <p class="text-center mb-1 text-base">
                        <strong>LWOP</strong>
                    </p>
                    <p>
                        <strong>Used</strong> - {{ leaveCredits[2].used }}
                    </p>
                </div>
                <div class="bg-orange-400 p-4 rounded-2xl text-white">
                    <p class="text-center mt-1">
                        <strong>Undertime</strong>
                    </p>
                    <p>
                        <strong>Used</strong> - {{ leaveCredits[5].used }}
                    </p>
                </div>
                <div class="bg-blue-500 p-4 rounded-2xl text-white">
                    <p class="text-center mb-1 text-base">
                        <strong>{{ employee?.empgender == 'FEMALE' ? 'Maternity' : 'Paternity' }}</strong>
                    </p>
                    <p>
                        <strong>Used</strong> - {{ employee?.empgender == 'FEMALE' ? leaveCredits[3].used : leaveCredits[4].used }}
                    </p>
                </div>
                <div class="bg-gray-500 p-4 rounded-2xl text-white">
                    <p class="text-center mb-1 text-base">
                        <strong>Bereavement</strong>
                    </p>
                    <p>
                        <strong>Used</strong> - {{ leaveCredits[5].used }}
                    </p>
                </div>
            </div>
        </div>
    </div>
</template>
