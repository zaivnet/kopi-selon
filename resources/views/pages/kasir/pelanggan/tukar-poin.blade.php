<div class="modal fade" id="modalPelanggan" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
   <div class="modal-dialog">
      <div class="modal-content">
         <div class="modal-header">
            <h5 class="modal-title" id="exampleModalLabel">Modal title</h5>
            <button type="button" class="close rounded-pill" data-bs-dismiss="modal" aria-label="Close">
               <i data-feather="x"></i>
            </button>
         </div>
         <form action="" method="POST" id="formPelanggan">
            <div class="modal-body">
               @csrf
               <input type="hidden" name="_method" id="methodField" value="POST">
               <div class="card shadow-sm border border-secondary mb-3" style="margin: 0; padding: 0;">
                  <div class="card-body">
                     <table style="font-size: 18px;">
                        <tr>
                           <th>Nama Pelanggan</th>
                           <td class="px-2">:</td>
                           <td id="nama"></td>
                        </tr>
                        <tr>
                           <th>Alamat</th>
                           <td class="px-2">:</td>
                           <td id="alamat"></td>
                        </tr>
                        <tr>
                           <th>Telepon/Wa</th>
                           <td class="px-2">:</td>
                           <td id="telepon"></td>
                        </tr>
                     </table>
                  </div>
               </div>
               <div class="card shadow-sm border border-secondary">
                  <form action="" method="post">
                     @csrf
                     @method('PUT')
                     <div class="card-body">
                        <div class="d-flex align-items-center justify-content-between mb-3">
                           <div>
                              <h6 class="mb-0 fw-semibold">Poin Yang Dimiliki</h6>
                           </div>
                        </div>
                        <div class="d-flex align-items-center justify-content-center gap-2 mb-4 p-3 border rounded">
                           <img src="https://cdn-icons-png.flaticon.com/512/10693/10693138.png" width="45" alt="Coin"
                              class="mb-1">
                           <div class="text-center">
                              <div class="fs-1 fw-bold text-warning" id="poin-text">
                              </div>
                           </div>
                        </div>
                        <div class="mb-3">
                           <label class="form-label fw-semibold">
                              Poin yang ingin ditukar
                           </label>
                           <input type="number" name="poin_ditukar" id="poin_ditukar"
                              class="form-control form-control-lg text-center" placeholder="Masukkan jumlah poin"
                              min="1" required>
                           <small class="text-muted">
                              Minimal 1 poin
                           </small>
                        </div>
                        <button class="btn btn-warning w-100 fw-semibold" type="submit"
                           onclick="return confirm('Tukar poin sekarang?')">
                           <i class="bi bi-arrow-repeat me-1"></i>
                           Tukar Poin
                        </button>
                     </div>
                  </form>
               </div>
            </div>
            <div class="modal-footer">
               <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                  <i class="bx bx-x d-block d-sm-none"></i>
                  <span class="d-none d-sm-block">Batal</span>
               </button>
            </div>
         </form>
      </div>
   </div>
</div>