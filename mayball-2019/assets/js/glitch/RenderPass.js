THREE.RenderPass=function(e,r,l,a,o){this.scene=e,this.camera=r,this.overrideMaterial=l,this.clearColor=a,this.clearAlpha=void 0!==o?o:1,this.oldClearColor=new THREE.Color,this.oldClearAlpha=1,this.enabled=!0,this.clear=!0,this.needsSwap=!1},THREE.RenderPass.prototype={render:function(e,r,l,a){this.scene.overrideMaterial=this.overrideMaterial,this.clearColor&&(this.oldClearColor.copy(e.getClearColor()),this.oldClearAlpha=e.getClearAlpha(),e.setClearColor(this.clearColor,this.clearAlpha)),e.render(this.scene,this.camera,l,this.clear),this.clearColor&&e.setClearColor(this.oldClearColor,this.oldClearAlpha),this.scene.overrideMaterial=null}};
