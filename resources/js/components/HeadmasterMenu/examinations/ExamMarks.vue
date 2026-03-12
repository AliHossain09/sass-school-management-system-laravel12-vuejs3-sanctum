<script setup lang="ts">
import HeadmasterLayout from '../../../layouts/HeadmasterLayout.vue'
import { computed, onMounted, ref, watch } from 'vue'
import axios from 'axios'
import { useToast } from 'vue-toastification'

const toast = useToast()

const loadingFilters = ref(false)
const loadingRows = ref(false)
const savingStudentId = ref<number | null>(null)

const examinations = ref<any[]>([])
const classes = ref<any[]>([])
const sections = ref<any[]>([])
const subjects = ref<any[]>([])

const examinationId = ref<number | ''>('')
const classId = ref<number | ''>('')
const sectionId = ref<number | ''>('')
const subjectId = ref<number | ''>('')

const context = ref<any | null>(null)
const rows = ref<any[]>([])

const authHeader = () => ({
  Authorization: `Bearer ${localStorage.getItem('token')}`,
})

const loadExamsAndClasses = async () => {
  loadingFilters.value = true
  try {
    const [examsRes, classesRes] = await Promise.all([
      axios.get('/api/examinations', { headers: authHeader(), params: { per_page: 200 } }),
      axios.get('/api/classes', { headers: authHeader(), params: { per_page: 200 } }),
    ])
    examinations.value = examsRes.data.data || []
    classes.value = classesRes.data.data || []
  } catch (err) {
    console.error(err)
  } finally {
    loadingFilters.value = false
  }
}

const loadSectionsAndSubjects = async () => {
  if (!classId.value) {
    sections.value = []
    subjects.value = []
    sectionId.value = ''
    subjectId.value = ''
    return
  }

  loadingFilters.value = true
  try {
    const [sectionsRes, subjectsRes] = await Promise.all([
      axios.get(`/api/sections/by-class/${classId.value}`, { headers: authHeader() }),
      axios.get(`/api/subjects/by-class/${classId.value}`, { headers: authHeader() }),
    ])

    sections.value = sectionsRes.data.data || []
    subjects.value = subjectsRes.data.data || []
    sectionId.value = ''
    subjectId.value = ''
  } catch (err) {
    console.error(err)
  } finally {
    loadingFilters.value = false
  }
}

watch(classId, () => {
  context.value = null
  rows.value = []
  loadSectionsAndSubjects()
})

onMounted(() => {
  loadExamsAndClasses()
})

const canFilter = computed(() => !!examinationId.value && !!classId.value && !!subjectId.value)

const filter = async () => {
  if (!examinationId.value) return toast.error('Select an exam')
  if (!classId.value) return toast.error('Select a class')
  if (!subjectId.value) return toast.error('Select a subject')

  loadingRows.value = true
  try {
    const res = await axios.get('/api/exam-marks/manage', {
      headers: authHeader(),
      params: {
        examination_id: examinationId.value,
        class_id: classId.value,
        section_id: sectionId.value || undefined,
        subject_id: subjectId.value,
      },
    })

    context.value = res.data.context
    rows.value = res.data.data || []
  } catch (err: any) {
    const firstError = err.response?.data?.errors ? Object.values(err.response.data.errors)[0]?.[0] : null
    toast.error(firstError || err.response?.data?.message || 'Failed to load')
  } finally {
    loadingRows.value = false
  }
}

const gradeText = (grade: any | null) => {
  if (!grade) return '-'
  const gp = typeof grade.grade_point === 'number' ? grade.grade_point.toFixed(2) : String(grade.grade_point)
  return `${grade.grade} (${gp})`
}

const saveRow = async (row: any) => {
  if (!examinationId.value || !classId.value || !subjectId.value) return
  if (row?.student?.id == null) return

  if (row.mark === '' || row.mark === null || row.mark === undefined) return toast.error('Mark is required')
  const mark = Number(row.mark)
  if (Number.isNaN(mark)) return toast.error('Mark must be a number')

  savingStudentId.value = row.student.id
  try {
    const res = await axios.post(
      '/api/exam-marks',
      {
        examination_id: examinationId.value,
        class_id: classId.value,
        section_id: sectionId.value || null,
        subject_id: subjectId.value,
        student_id: row.student.id,
        mark,
        comment: row.comment || null,
      },
      { headers: authHeader() }
    )

    row.mark = res.data.data.mark
    row.comment = res.data.data.comment
    row.grade = res.data.data.grade
    toast.success('Mark saved')
  } catch (err: any) {
    const firstError = err.response?.data?.errors ? Object.values(err.response.data.errors)[0]?.[0] : null
    toast.error(firstError || err.response?.data?.message || 'Failed to save')
  } finally {
    savingStudentId.value = null
  }
}
</script>

<template>
  <HeadmasterLayout>
    <div class="flex shadow-xl rounded mb-6 p-4 bg-white gap-2">
      <p class="text-gray-700">Headmaster ></p>
      <p class="text-gray-700">Examination ></p>
      <p class="text-gray-700">Mark</p>
    </div>

    <div class="p-6 shadow-2xl bg-gray-50 rounded">
      <div class="flex flex-wrap gap-3 items-center justify-between mb-6">
        <select v-model="examinationId" class="border rounded-md px-3 py-2 text-black min-w-[220px]" :disabled="loadingFilters">
          <option value="">Select exam</option>
          <option v-for="e in examinations" :key="e.id" :value="e.id">{{ e.name }}</option>
        </select>

        <select v-model="classId" class="border rounded-md px-3 py-2 text-black min-w-[220px]" :disabled="loadingFilters">
          <option value="">Select class</option>
          <option v-for="c in classes" :key="c.id" :value="c.id">{{ c.name }}</option>
        </select>

        <select v-model="sectionId" class="border rounded-md px-3 py-2 text-black min-w-[160px]" :disabled="loadingFilters || !classId">
          <option value="">All sections</option>
          <option v-for="s in sections" :key="s.id" :value="s.id">{{ s.name }}</option>
        </select>

        <select v-model="subjectId" class="border rounded-md px-3 py-2 text-black min-w-[220px]" :disabled="loadingFilters || !classId">
          <option value="">Select subject</option>
          <option v-for="s in subjects" :key="s.id" :value="s.id">{{ s.name }}</option>
        </select>

        <button
          @click="filter"
          class="bg-gray-600 text-white px-5 py-2 rounded hover:bg-gray-700"
          :disabled="loadingRows || loadingFilters || !canFilter"
        >
          {{ loadingRows ? 'Loading...' : 'Filter' }}
        </button>
      </div>

      <div v-if="context" class="flex justify-center mb-6">
        <div class="bg-gray-600 text-white rounded px-10 py-5 text-center min-w-[360px]">
          <div class="font-semibold text-lg mb-1">Manage marks</div>
          <div class="text-sm">Class : {{ context.class?.name }}</div>
          <div class="text-sm">Section : {{ context.section?.name || 'All' }}</div>
          <div class="text-sm">Subject : {{ context.subject?.name }}</div>
        </div>
      </div>

      <div v-if="loadingRows" class="text-center text-gray-600">Loading...</div>

      <div v-if="!loadingRows" class="overflow-x-auto">
        <table class="w-full border-collapse border border-gray-200 bg-white rounded-md">
          <thead class="bg-blue-900 text-white">
            <tr>
              <th class="border border-gray-200 px-4 py-2 text-left">Student name</th>
              <th class="border border-gray-200 px-4 py-2 text-left">Mark</th>
              <th class="border border-gray-200 px-4 py-2 text-left">Grade point</th>
              <th class="border border-gray-200 px-4 py-2 text-left">Comment</th>
              <th class="border border-gray-200 px-4 py-2 text-left">Action</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="row in rows" :key="row.student.id" class="hover:bg-gray-50">
              <td class="border border-gray-200 px-4 py-2">{{ row.student.name }}</td>
              <td class="border border-gray-200 px-4 py-2">
                <input v-model="row.mark" type="number" min="0" max="100" class="input text-black" />
              </td>
              <td class="border border-gray-200 px-4 py-2">{{ gradeText(row.grade) }}</td>
              <td class="border border-gray-200 px-4 py-2">
                <input v-model="row.comment" placeholder="comment" class="input text-black" />
              </td>
              <td class="border border-gray-200 px-4 py-2">
                <button
                  class="bg-green-600 text-white px-3 py-1 rounded text-sm hover:bg-green-700"
                  @click="saveRow(row)"
                  :disabled="savingStudentId === row.student.id"
                  title="Save"
                >
                  {{ savingStudentId === row.student.id ? 'Saving...' : 'Save' }}
                </button>
              </td>
            </tr>
            <tr v-if="rows.length === 0">
              <td colspan="5" class="text-center py-6 text-gray-600">No students found</td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>
  </HeadmasterLayout>
</template>

<style scoped>
.input {
  border: 1px solid #ccc;
  padding: 8px 12px;
  border-radius: 4px;
  width: 100%;
  color: #000;
}
</style>

