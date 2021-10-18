<style>
    .l__qdo_form_wrapper{
        display: table;
        width: 100%;
        padding: 0 15px;
    }
</style>

<div class="l__qdo_form_wrapper">
    <div class="row">
        <?php
        date_default_timezone_set('America/Fortaleza');
        $agora = new DateTime();
        $inicio_da_submissao = new DateTime(get_field('inicio_da_submissao'));
        $fim_da_submissao = new DateTime(get_field('fim_da_submissao'));
        $clear = false;

        if ($inicio_da_submissao <= $agora && $agora <= $fim_da_submissao) :
            $response = apply_filters('qdo_jobs_send_form', false);

            if ($response !== false):
                $clear = $response['status'];
                $class = $response['class'];
                $msg = $response['msg'];
                ?>
                <div class="alert alert-<?= $class ?>" role="alert"><?= $msg ?></div>
                <?php
            endif;
            ?>

            <div class="col-lg-12">
                <h1>Formulário</h1>
                <h3><?= get_post()->post_title ?></h3>
            </div>

            <div class="col-lg-12">
                <?= get_field('resumo') ?>
            </div>

            <div class="col-lg-12">
                <hr>
            </div>

            <div class="col-lg-12">
                <form action="#" method="POST" enctype="multipart/form-data">
                    <input type="hidden" name="qdo-jobs" value="<?= QDO_JOB_PREFIX_ID . get_post()->ID ?>">
                    <h3>Dados Pessoais</h3>
                    <?php
                    foreach (get_field('informacoes_necessarias') as $info) :
                        $info_field = explode(':', $info);
                        $f_name = QDO_JOB_PREFIX_INPUT_NAME . qdo_jobs_sanitize_text($info_field[1]);
                        ?>
                        <div class="input-group">
                            <span class="input-group-addon"><?= $info_field[1] ?>*</span>
                            <input type="<?= $info_field[0] ?>" value="<?= qdo_jobs_chkInput(INPUT_POST, $f_name, true, $clear) ?>" name="<?= $f_name ?>" class="form-control" required>
                        </div>
                    <?php endforeach; ?>

                    <hr>
                    <h3>Arquivos</h3>

                    <?php
                    $arquivos_aceitos = is_array(get_field('arquivos_aceitos')) ? join(', ', get_field('arquivos_aceitos')) : '';
                    for ($i = 1; $i <= intval(get_field('maximo_de_arquivos')); $i++) :
                        ?>
                        <div class="input-group">
                            <span class="input-group-addon">Arquivo <?= $i ?></span>
                            <input type="file" name="<?= QDO_JOB_PREFIX_INPUT_FILE_NAME . $i ?>" class="form-control" accept="<?= $arquivos_aceitos ?>">
                        </div>
                    <?php endfor; ?>

                    <hr>
                    <div class="col-lg-6 col-md-6 col-sm-12">
                        <div class="g-recaptcha" data-sitekey="SITE_KEY"></div>
                    </div>
                    <div class="col-lg-6 col-md-6 col-sm-12">
                        <div class="text-center">
                            <br>
                            <button type="submit" class="btn btn-primary btn-lg">Enviar</button>
                        </div>
                    </div>
                </form>
            </div>

        <?php else : ?>

            <div class="alert alert-warning" role="alert">Este formulário está fora do período de submissão.</div>

        <?php endif; ?>
    </div>
</div>