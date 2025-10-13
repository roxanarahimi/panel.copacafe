<template>
    <transition name="route" mode="out-in" appear>
        <section>
            <h3 class="mb-5">ویرایش محصول</h3>

            <div class="row mt-3">
                <div class="col-12 mb-3">
                    <div class="card" v-if="data?.length">
                        <div class="card-body">
                            <form id="editForm">
                                <div class="row">
                                    <div class="col-12 mb-3">
                                        <label class="form-label">تصویر روبرو</label><br/>
                                        <image-cropper name="1" caption="" :hasCaption="hasCaption"
                                                   :isPng="isPng"    :isRequired="imgRequired" :aspect="aspect" :src="data.image1"/>
                                        <div id="image1Help" class="form-text error"></div>
                                    </div>
                                    <div class="col-12 mb-3">
                                        <label class="form-label">تصویر پشت</label><br/>
                                        <image-cropper name="2" caption="" :hasCaption="hasCaption"
                                                   :isPng="isPng"    :isRequired="imgRequired" :aspect="aspect" :src="data.image2"/>
                                        <div id="image2Help" class="form-text error"></div>
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-md-4 mb-3">
                                        <label for="title" class="form-label">عنوان</label>
                                        <input @input="updateData" type="text" :class="{hasError: errors.title}"
                                               class="form-control"
                                               id="title" :value="data.title" aria-describedby="titleHelp" required>
                                        <div id="titleHelp" class="form-text error"></div>
                                        <p class="form-text error m-0" v-for="e in errors.title">{{ e }}</p>

                                    </div>

                                    <div class="col-md-4 mb-3">
                                        <label for="subTitle" class="form-label">زیرنویس</label>
                                        <input @input="updateData" type="text" :value="data.subTitle" :class="{hasError: errors.subTitle}" class="form-control"
                                               id="subTitle" aria-describedby="subTitleHelp" >
                                        <div id="subTitleHelp" class="form-text error"></div>
                                        <p class="form-text error m-0" v-for="e in errors.subTitle">{{ e }}</p>
                                    </div>

<!--                                    <div class="col-md-3 mb-3">-->
<!--                                        <label for="index" class="form-label">اولویت نمایش(0 بیشترین اولویت)</label>-->
<!--                                        <input @input="updateData" type="text" :value="data.index" :class="{hasError: errors.index}" class="form-control en"-->
<!--                                               id="index" aria-describedby="indexHelp">-->
<!--                                        <div id="indexHelp" class="form-text error"></div>-->
<!--                                        <p class="form-text error m-0" v-for="e in errors.index">{{ e }}</p>-->
<!--                                    </div>-->

                                    <div class="col-md-4 col-lg-4 mb-3">
                                        <label for="category" class="form-label">دسته</label>
                                        <select @change="updateData" class="form-select" id="category"
                                                aria-describedby="categoryHelp"
                                                aria-label="category" >
                                            <option value=""></option>
                                            <option :selected="data.category.id == category.id"
                                                    v-for="category in categories" :key="category.id"
                                                    :value="category.id">
                                                {{ category.title }}
                                            </option>
                                        </select>
                                        <div id="categoryHelp" class="form-text error"></div>
                                    </div>

<!--                                  <div class="col-md-12 col-lg-12 mb-3">-->
<!--                                    <label for="link" class="form-label">لینک خرید</label>-->
<!--                                    <input type="text" :class="{hasError: errors.link}"-->
<!--                                          :value="data.link" class="en form-control text-start" id="link">-->
<!--                                    <div id="linkHelp" class="form-text error"></div>-->
<!--                                    <p class="form-text error m-0" v-for="e in errors.link">{{ e }}</p>-->

<!--                                  </div>-->
                                    <div class="col-md-12 mb-3">
                                        <label class="form-label" for="text">متن</label>
                                        <textarea @input="watchTextAreas" :class="{hasError: errors.text}"
                                                  aria-describedby="textHelp" class="form-control text-start" id="text">{{ data.text}}</textarea>
                                        <div id="textHelp" class="form-text error"></div>
                                        <p class="form-text error m-0" v-for="e in errors.text">{{ e }}</p>

                                    </div>

                                    <div class="col-md-12 mb-3">
                                        <BtnSubmit @click.prevent="updateInfo">
                                            ویرایش
                                        </BtnSubmit>
                                    </div>
                                </div>

                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </section>

    </transition>
</template>

<script>
import ImageCropper from '../../components/ImageCropper';
import App from '../App';
import BtnSubmit from "../../components/BtnSubmit";
import Multiselect from '@vueform/multiselect'


export default {
    components: {ImageCropper, App, BtnSubmit, Multiselect},
    data() {
        return {
            id: this.$route.params.id,
            data: {},
            categories: [],
            errors: [],
            image_codes: [],
            image_names: [],
            imgRequired: false,
            hasCaption: false,
            aspect: 1/1,
            isPng: true,
            isDefined: false,
            enableClick: true,
            features: [],
            images: [],

            value: [],
            allProducts: []
        }
    },

    created() {
        this.loadCategories();
        this.loadProduct();
        this.loadProducts();
    },

    methods: {
        loadProduct() {

            axios.get('/api/panel/product/' + this.id)
                .then((response) => {
                    console.log(response.data);
                    this.data = response.data;
                    if (this.data?.images) {
                        this.images = this.data.images;
                    }
                })
                .then(() => {
                    this.isDefined = true;
                })
                .then(() => {
                    this.value = this.data.related_products;
                })
                .then(() => {
                    this.watchTextAreas();
                })
                .catch();
        },
        loadProducts() {

            axios.get('/api/panel/product?page=1&perPage=1000&search=')
                .then((response) => {
                    this.allProducts = response.data.data;
                    this.allProducts = this.allProducts.filter((item)=>item.id != this.id);
                })
                .catch()
        },
        loadCategories() {
            axios.get('/api/panel/category/product?page=1&perPage=100000')
                .then((response) => {
                    this.categories = response.data.data;
                })
                .catch();
        },
        updateInfo() {


            this.errors = [];
            let emptyFieldsCount = 0;

            let req = document.querySelectorAll('[required]');
            req.forEach((element) => {
                if (element.value === "") {
                    element.classList.add('hasError');
                    element.nextSibling.innerHTML = "فیلد اجباری";
                    emptyFieldsCount++;
                } else {
                    element.classList.remove('hasError');
                    element.nextSibling.innerHTML = "";
                }
            });

            if (emptyFieldsCount === 0) {

                axios.post('/api/panel/product/' + this.$route.params.id,
                    {
                        // image: document.getElementById('Image_index_code').value,
                        id: this.$route.params.id,
                        image1: document.getElementById('Image_1_code').value,
                        image2: document.getElementById('Image_2_code').value,
                        title: document.getElementById('title').value,
                        subTitle: document.getElementById('subTitle').value,
                        product_category_id: document.getElementById('category').value,
                        text: document.getElementById('text').value,
                        // index: document.getElementById('index').value,
                        // link: document.getElementById('link').value,
                    })
                    .then((response) => {
                        console.log('res', response);
                        if (response.status === 200) {
                            setTimeout(() => {
                                this.$router.push({path: '/panel/product/' + this.id});
                            }, 1000);
                        }
                    })
                    .catch((error) => {
                        document.querySelector('#submit').removeAttribute('disabled');
                        document.querySelector('.loader-sm').classList.add('d-none');

                        if (error.response.status === 422) {
                            let errorList = Array(error.response.data);
                            for (var i = 0; i < errorList.length; i++) {
                                this.errors = errorList[i];
                            }

                        } else if (error.response.status === 500) {
                            if (error.response.data.message.includes("SQLSTATE")) {
                                console.error('خطای پایگاه داده');

                                function showAlertSql() {
                                    setTimeout(() => {
                                        alert(error.response.data.message);
                                    }, 200);
                                }

                                showAlertSql();
                            } else {
                                function showAlert500() {
                                    setTimeout(() => {
                                        alert(error.message + ' '
                                            + error.response.data.message);
                                    }, 200);
                                }

                                showAlert500();
                            }

                        } else {
                            function showAlert() {
                                setTimeout(() => {
                                    alert(error.message);
                                }, 200);
                            }

                            showAlert();

                        }
                    });
            }
        },

        updateData() {
            this.data.title = document.getElementById('title').value;
            this.data.subTitle = document.getElementById('subTitle').value;
            this.data.text = document.getElementById('text').value;
            this.data.product_category_id = document.getElementById('category').value;
            // this.data.index = document.getElementById('index').value;
            // this.data.link = document.getElementById('link').value;
        },
        watchTextAreas() {
            let txt = document.querySelector("#text");
            txt?.setAttribute("style", "height:" + (txt.scrollHeight + 20) + "px;overflow-y:hidden;");
            txt?.addEventListener("input", changeHeight, false);

            function changeHeight() {
                this.style.height = "auto";
                this.style.height = (this.scrollHeight) + "px";
            }
        },
        addFeature() {
            this.features.push('{"label": "", "value": "", "unit": ""}');
        },
        removeFeature(index) {

            this.features.splice(index, 1)
        },
        updateFeatures() {
            this.features = [];
            for (let i = 0; i < document.getElementsByName('featureLabel').length; i++) {
                this.features.push({
                    "label": document.getElementsByName('featureLabel')[i].value.toString(),
                    "value": document.getElementsByName('featureValue')[i].value.toString(),
                    "unit": document.getElementsByName('featureUnit')[i].value.toString()
                });
            }

        },

    }
}
</script>
<style src="@vueform/multiselect/themes/default.css"></style>

<style>
span i {
    cursor: pointer;
}

.en {
    direction: ltr !important;
    text-align: left !important;
}

.multiselect-tags-search{
    background-color: transparent !important;
}
.multiselect-tag{
    background-color: #0d6efd !important;
}
.multiselect.is-active
{
    box-shadow: none !important;
}
</style>
