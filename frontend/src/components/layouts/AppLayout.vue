<template>
    <div class="layout-shell">
            <aside class="sidebar">
                <div class="sidebar-header">
                    <img src="\public\logo-paddlehub-grande.png"alt="Logo" class="logo">
                </div>

                <nav class="sidebar-nav">
                    <ul>
                        <li>
                            <a>
                                Home
                            </a>
                        </li>

                        <li>
                            <a>
                                Mapa
                            </a>
                        </li>

                        <li>
                            <a>
                                Eventos
                            </a>
                        </li>

                        <li>
                            <a>
                                Configuracao
                            </a>
                        </li>
                    </ul>
                </nav>

                <div class="sidebar-footer">
                    <a>Perfil</a>
                    <a>Sair</a>
                </div>
            </aside>
        </div>
        <div class="main">
            <slot></slot>
        </div>
</template>
<script setup lang="ts">
import { RouterLink } from 'vue-router'
</script>
<style scoped>
.layout-shell {
    display: flex;
    min-height: 100vh;
    background: rgba(33, 86, 109, 0.096);

}

.sidebar {
    width: 300px;
    border: 1px solid rgba(69, 199, 255, 0.096);
    

}

.main {
    /* background-color: blueviolet; */
    flex: 1;
}
.sidebar-header {
  display: flex;
  justify-content: center;
}
.logo{
    max-width: 250px;
    
}
</style>