<template>
  <!--    <transition name="route" mode="out-in" appear>-->

  <all-data-container :model="model"
                      :title="title" :tooltip_new="tooltip_new">
    <h3 class="mb-5">{{ title }}
      <router-link v-if="tooltip_new" :to="'/panel/new/'+ model" class="text-dark">
                    <span :title="tooltip_new" class="px-3 d-inline-block align-middle"><i
                        class="bi bi-plus-circle-fill p-0 mt-2 m-0" style="font-size: 15px"></i></span>
      </router-link>
<!--        <router-link v-if="this.$route.name == 'productAllData'" to="/panel/priority/products">-->
<!--            <span  class=" d-inline-block align-middle bg-dark text-light rounded rounded-circle py-1 text-center"-->
<!--                           style="width: 16px !important; height: 16px !important; overflow: hidden !important" title="اولویت">-->
<!--            <i style="font-size: 10px !important; line-height: 10px; margin-top: -20px !important; " class="align-top bi bi-arrow-down-up m-0"></i>-->
<!--        </span>-->
<!--        </router-link>-->
    </h3>

    <loader/>

    <div class="mb-3 col-lg-6" :class="{'d-none': model === 'foodSlide'}">
      <label for="search" class="form-label">جستوجو</label>
      <input @input="loadData" type="text" class="form-control" id="search" >
    </div>
    <div class="col-12 mb-3" v-if="allData && allData.length">
      <Suspense>
        <component :is="model+'sTable'" :allData="allData" :page="page" :model="model" :loadData="loadData"/>
        <template #fallback>
          <loader/>
        </template>
      </Suspense>
    </div>
    <pagination :page="page" :pages="pages" :total="total" :labels="labels" :load="loadData"/>

  </all-data-container>
  <!--    </transition>-->
</template>
<script>
import {ref, onMounted, onUpdated, onUnmounted} from 'vue';
import Loader from "../components/Loader";
import AllDataContainer from "./AllDataContainer";
import Pagination from "../components/Pagination";
import productsTable from "./product/productsTable";
import usersTable from "./user/usersTable";
import adminsTable from "./admin/adminsTable";
import {useRoute} from "vue-router";


export default {
  components: {
    Loader, AllDataContainer, Pagination,
    productsTable,  usersTable,adminsTable
  },
  setup() {
    const model = ref();
    const title = ref('');
    const tooltip_new = ref('');
    const page = ref();
    const pages = ref();
    const total = ref();
    const labels = ref([]);
    const allData = ref([]);

    const showDeleteModal = async (id) => {
      await AllDataContainer.setup().showDeleteModal(id);
    }
    const deleteInfo = async (model, id) => {
      await AllDataContainer.setup().deleteInfo(model, id);
      await loadData();
    };
    const activeToggle = async (model, id) => {
      await AllDataContainer.setup().activeToggle(model, id);
      await loadData(page.value);
    };
    const loadData = async (p) => {
      if (p === undefined) {
        page.value = 1;
      } else if (1 <= p && p <= pages.value) {
        page.value = p;
      }
      document.querySelector('#loader').classList.remove('d-none');

      let perPage = document.querySelector('#perPage')?.value || 100;
      let search = document.querySelector('#search')?.value || '';
      axios.get('/api/panel/' + model.value + '?page=' + page.value + '&perPage=' + perPage+ '&search='+search)
          .then((response) => {
            allData.value = response.data.data;
            pages.value = response.data.pages;
            total.value = response.data.total;
            labels.value = response.data.labels;
            title.value = response.data.title;
            tooltip_new.value = response.data.tooltip_new;
            document.querySelector('#loader').classList.add('d-none');
            if (pages.value > 1) {
              document.querySelector('.paginate')?.classList.remove('d-none');
            }
            if (allData.value) {
              document.querySelector('#loader').classList.add('d-none');
              // setTimeout(() => {
              //     document.querySelector('#msg').innerHTML = '<p class="fw-bold">موردی پیدا نشد</p>';
              // }, 3000)
            }
          }).catch();


      setTimeout(() => {
        document.querySelector('#loader')?.classList.add('d-none');
      }, 4000)
    };

    onUpdated(() => {
      model.value = useRoute().name.replace('AllData', '');
      allData.value = [];
      loadData();
      document.querySelector('.paginate')?.classList.add('d-none');
      // alert(model.value)
    });
    onMounted(() => {
      loadData();
    });

    return {
      model, title, tooltip_new, allData, page, pages, total, labels,
      loadData, showDeleteModal, deleteInfo, activeToggle
    };
  },

}
</script>
<style>
.date_cell {
  direction: ltr !important;
  text-align: right !important;
}

.text_cell {
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
  max-width: 30ch;
}

.active_cell {
  width: 60px;
  text-align: center;
}


</style>
