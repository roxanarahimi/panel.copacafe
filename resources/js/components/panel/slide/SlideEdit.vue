<template>
    <transition name = "route" mode = "out-in" appear>
        <section>
            <h3 class = "mb-5">ویرایش اسلاید</h3>

            <div class = "row mt-3">
                <div class = "col-12 mb-3">
                    <div v-if = "data?.length" class = "card">
                        <div class = "card-body">
                            <form id = "editForm">
                                <div class = "row">
                                    <div class = "col-12 mb-3">
                                        <label class = "form-label">تصویر</label><br/>
                                        <image-cropper name = "" :src = "data.image" caption = "" :hasCaption = "hasCaption" :isRequired = "imgRequired" :aspect = "aspect"/>
                                        <div id = "imageHelp" class = "form-text error"></div>
                                    </div>
                                </div>
                                <div class = "row">
<!--                                    <div class = "col-md-3 mb-3">-->
<!--                                        <label for = "title" class = "form-label">عنوان</label>-->
<!--                                        <input type = "text" :class = "{hasError: errors.title}" class = "form-control" id = "title" :value = "data.title" aria-describedby = "titleHelp" required>-->
<!--                                        <div id = "titleHelp" class = "form-text error"></div>-->
<!--                                        <p class = "form-text error m-0" v-for = "e in errors.title">{{ e }}</p>-->

<!--                                    </div>-->
<!--                                    <div class = "col-md-6 mb-3">-->
<!--                                        <label for = "subTitle" class = "form-label">زیرنویس</label>-->
<!--                                        <input type = "text" :class = "{hasError: errors.subTitle}" class = "form-control text-start" :value = "data.subTitle" id = "subTitle" required>-->
<!--                                        <div id = "subTitleHelp" class = "form-text error"></div>-->
<!--                                        <p class = "form-text error m-0" v-for = "e in errors.subTitle">{{ e }}</p>-->
<!--                                    </div>-->
<!--                                    <div class = "col-md-3 mb-3">-->
<!--                                        <label for = "link" class = "form-label">لینک</label>-->
<!--                                        <input type = "text" :class = "{hasError: errors.link}" class = "form-control" id = "link" :value = "data.link" aria-describedby = "linkHelp" required>-->
<!--                                        <div id = "linkHelp" class = "form-text error"></div>-->
<!--                                        <p class = "form-text error m-0" v-for = "e in errors.link">{{ e }}</p>-->

<!--                                    </div>-->


                                    <div class = "col-md-12 mb-3">
                                        <button class = "btn btn-primary" @click.prevent = "updateInfo" type = "submit">
                                            <!--                                        <button class = "btn btn-primary" type = "submit">-->
                                            ویرایش
                                        </button>
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
    // import {toArray} from "../../../public/cropperjs/src/js/utilities";

    export default {
        components: {ImageCropper},
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
                aspect: 1500 / 621,
                isDefined: false,
                enableClick: true,
                features: [],
                progress: 0,
            }
        },

        mounted() {


            this.loadData();

        },

        methods: {
            async loadData() {

                await axios.get('/api/panel/slide/' + this.id)
                    .then((response) => {
                        this.data = response.data;
                        if (this.data.features) {
                            for (let i = 0; i < JSON.parse(this.data.features).length; i++) {
                                this.features.push(JSON.parse(this.data.features)[i]);
                            }
                        }
                    })
                    .then(() => {
                        this.isDefined = true;
                    })
                    .catch();
            },
            async updateInfo() {

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
                    let features = [];
                    for (let i = 0; i < document.getElementsByName('featureLabel').length; i++) {
                        features.push('{"label": "' + document.getElementsByName('featureLabel')[i].value + '", "value": "' + document.getElementsByName('featureValue')[i].value + '"}');
                    }
                    if (document.getElementsByName('featureLabel').length === 0) {
                        features = '[]';
                    } else {
                        features = '[' + features.toString() + ']';
                    }
                    await axios.post('/api/panel/slide/' + this.$route.params.id,
                        {
                            image: document.getElementById('Image__code').value,
                            // title: document.getElementById('title').value,
                            // subTitle: document.getElementById('subTitle').value,
                            // link: document.getElementById('link').value,

                        })
                        .then((response) => {
                            if (response.status === 200) {
                                 setTimeout(() => {
                                    // this.$router.push({path: '/panel/slide/'+ this.id });
                                    this.$router.push({path: '/panel/slides'});
                                }, 1000);
                            }
                        })
                        .catch((error) => {
                            if (error.response.status === 422) {
                                let errorList = Array(error.response.data);
                                for (var i = 0; i < errorList.length; i++) {
                                    // console.log('i', errorList[i]);
                                    this.errors = errorList[i];
                                }
                                setTimeout(() => {
                                }, 1000);

                            } else if (error.response.status === 500) {
                                if (error.response.data.message.includes("SQLSTATE")) {
                                    console.error('خطای پایگاه داده');

                                    async function showAlertSql() {
                                        setTimeout(() => {
                                            alert(error.response.data.message);
                                        }, 200);
                                    }

                                    showAlertSql();
                                } else {
                                    async function showAlert500() {
                                        setTimeout(() => {
                                            alert(error.message + ' '
                                                + error.response.data.message);
                                        }, 200);
                                    }

                                    showAlert500();
                                }

                            } else {
                                async function showAlert() {
                                    setTimeout(() => {
                                        alert(error.message);
                                    }, 200);
                                }

                                showAlert();

                            }
                        });
                }
            },
        }
    }
</script>
<style>
    span i {
        cursor: pointer;
    }


</style>
