﻿using System.Collections;
using System.Collections.Generic;
using UnityEngine;
using UnityEngine.UI;
using UnityEngine.Video;
using UnityStandardAssets.Characters.FirstPerson;
using System;

public class Player_Custom_Script : MonoBehaviour {

	private Canvas canvas_ti;
	private Canvas canvas_v;
	private Canvas canvas_a;
	private Camera camera, camera2;
	private GameObject active;
	private Boolean flag_artifact_rotate_scale = false;

	void Start () {

		canvas_ti = GameObject.Find ("canvas_ti").GetComponent<Canvas> ();
		canvas_v = GameObject.Find ("canvas_v").GetComponent<Canvas> ();
		canvas_a = GameObject.Find ("canvas_a").GetComponent<Canvas> ();

		camera = GameObject.Find ("FirstPersonCharacter").GetComponent<Camera> ();

		// camera 2 is a camera on a separate place for viewing the selected 3D model
		camera2 = GameObject.Find ("camera2").GetComponent<Camera> ();

		Button bt_it_close = GameObject.Find ("bt_ti_close").GetComponent<Button>();
		bt_it_close.onClick.AddListener( activateObjectAgain );
	}

	// When player runs over an item
	void OnTriggerEnter(Collider other)
	{
		checkTag (other.gameObject);
	}

	// When player goes away from an item
	void OnTriggerExit(Collider other)
	{
		if (other.gameObject.tag == "poi_imagetext") {
			canvas_ti.enabled = false;

			// Make the obj to appear
			other.gameObject.transform.GetChild(0).transform.GetChild(0).GetComponent<MeshRenderer>().enabled = true;

			playPlayer ();

		} else if (other.gameObject.tag == "poi_video") {
			canvas_v.enabled = false;

			GameObject.Find ("panel_v").GetComponent<VideoPlayer> ().Stop ();

			GameObject.Find ("panel_v").GetComponent<AudioSource> ().Stop ();

			// Make the obj to appear
			other.gameObject.transform.GetChild(0).transform.GetChild(0).GetComponent<MeshRenderer>().enabled = true;

			playPlayer ();

		} else if (other.gameObject.tag == "poi_artefact") {
			// playPlayer ()  is not possible because FPS is disabled. Done with button that calls closeArtefactView


		}
	}

	// Close the 3D viewer of the artifact and the canvas with related info
	public void closeArtefactView(){

		canvas_a.enabled = false;

		camera.enabled = true;
		camera.fieldOfView = 60;
		camera2.enabled = false;

		// Destroy the copied object
		Destroy (GameObject.Find ("meshcontainer").transform.GetChild (0).gameObject);

		// Enable FPS
		playPlayer();
		appearExitButton ();
	}



	void Update(){
		if (camera2.isActiveAndEnabled ) {

			if (Input.GetButtonDown("Fire1"))
			{
				if (Input.mousePosition [0] < Screen.width / 2)
					flag_artifact_rotate_scale = true;
				else
					flag_artifact_rotate_scale = false;
			}

			if (flag_artifact_rotate_scale){

				// Rotation
				GameObject.Find ("meshcontainer").transform.Rotate (
					new Vector3 (- Input.GetAxis ("Mouse Y") * Time.deltaTime * 200, - Input.GetAxis ("Mouse X") * Time.deltaTime * 200, 0)
				);

				// Scaling
				if (Input.GetAxis ("Mouse ScrollWheel") != 0) {
					Vector3 targetScale = GameObject.Find ("meshcontainer").transform.localScale - 30 * Input.GetAxis ("Mouse ScrollWheel") * (new Vector3 (1, 1, 1));

					if (targetScale.x > 0)
						GameObject.Find ("meshcontainer").transform.localScale =
							Vector3.Lerp (GameObject.Find ("meshcontainer").transform.localScale, targetScale, 10 * Time.deltaTime);
				}
			}
		}


		if (camera.isActiveAndEnabled) {

			// Scroll wheel zoom
			if (Input.GetAxis ("Mouse ScrollWheel") != 0) {
				if (camera != null) {
					if (camera.fieldOfView <= 60 && camera.fieldOfView > 2) {
						camera.fieldOfView -= 10 * Input.GetAxis ("Mouse ScrollWheel");
					} else if (camera.fieldOfView <= 2) {
						camera.fieldOfView = 2;
					} else
						camera.fieldOfView = 60;
				}
			}


			// Pick by raycasting
			if (Input.GetMouseButtonDown(0)) {
				RaycastHit[] hits;

				hits = Physics.RaycastAll(camera.ScreenPointToRay(Input.mousePosition), 100.0F);

				for (int i = 0 ; i < hits.Length; i++) {
					if (hits [i].transform.gameObject.tag != "Untagged") {
						checkTag (hits [i].transform.gameObject);
						break;
					}
				}
			}

			if (Input.GetKeyDown ("s") || Input.GetKeyDown ("w"))
				activateObjectAgain ();
		}

	}



	// Closes poi_it and poi_v canvases and reshows their objs
	void activateObjectAgain(){
		canvas_ti.enabled = false;
		canvas_v.enabled = false;

		if (active)
			active.transform.GetChild(0).transform.GetChild(0).GetComponent<MeshRenderer>().enabled = true;

		playPlayer ();
	}


	/* Check the tag of the clicked or collided object */
	void checkTag(GameObject go){

		if (go.tag == "poi_imagetext") {

			freezePlayer ();

			active = go;

			// Make the obj to dissapper in order not to overlay on canvas
			go.transform.GetChild(0).transform.GetChild(0).GetComponent<MeshRenderer>().enabled = false;

			// Get the name of the sprite from the collided object
			string spriteName = go.GetComponent<DisplayPOI_Script> ().imageSpriteNameToShow;

			// Set sprite and text in panel
			Sprite image_sprite = Resources.Load<Sprite> (spriteName);

			if (image_sprite){
				GameObject.Find ("img_ti").GetComponent<Image> ().sprite = image_sprite;
			} else {
				Debug.Log( spriteName + " was not found. Are you sure you have imported it in Resources folder?" );
			}

			// Set the text
			GameObject.Find ("txt_ti").GetComponent<Text> ().text = go.GetComponent<DisplayPOI_Script> ().textToShow;

			// Get the title of the poi to put as a text in the title_txt_ti in canvas_ti
			GameObject.Find ("title_txt_ti").GetComponent<Text> ().text = go.name;

			canvas_ti.enabled = true;

		} else if (go.tag == "poi_video") {

			freezePlayer ();

			active = go;

			// Make the obj to dissapper in order not to overlay on canvas
			go.transform.GetChild(0).transform.GetChild(0).GetComponent<MeshRenderer>().enabled = false;

			// Get the name of the sprite from the collided object
			string videoName = go.GetComponent<DisplayPOI_Script> ().videoToShow;
			string videoUrlName = go.GetComponent<DisplayPOI_Script> ().videoUrlToShow;

			// Put the video to the video player
			VideoPlayer videoPlayer = GameObject.Find ("panel_v").GetComponent<VideoPlayer> ();
			videoPlayer.playOnAwake  = false;


			#if UNITY_WEBGL
			videoPlayer.source = VideoSource.Url;
			videoPlayer.url= videoUrlName; //"http://127.0.0.1:8080/Videos/Hanna.mp4";
			#else
			videoPlayer.source = VideoSource.VideoClip;
			videoPlayer.clip = Resources.Load<VideoClip> (videoName);
			#endif

			// Set video audio to audioSource
			AudioSource audioSource = GameObject.Find ("panel_v").GetComponent<AudioSource> ();

			videoPlayer.audioOutputMode = VideoAudioOutputMode.AudioSource;
			videoPlayer.EnableAudioTrack (0, true);

			audioSource.playOnAwake  =  false;
			videoPlayer.SetTargetAudioSource (0, audioSource);



			if (videoName.Length > 0 || videoUrlName.Length >0) {
				videoPlayer.Play ();
				audioSource.Play ();
				canvas_v.enabled = true;
			} else {
				Debug.Log (videoName + " or " + videoUrlName  + " was not found.");
			}

		} else if (go.tag == "poi_artefact") {

			freezePlayer ();

			canvas_a.enabled = true;

			// show text on canvas by fetching it from the collided object
			GameObject.Find ("txt_a").GetComponent<Text> ().text = go.GetComponent<DisplayPOI_Script> ().textToShow;

			Transform collidedObjectTransform = go.transform.GetChild (0);


			GameObject.Find ("meshcontainer").transform.localScale = new Vector3 (10, 10, 10);

			// Copy mesh
			Instantiate(collidedObjectTransform, GameObject.Find ("meshcontainer").transform);

			camera.enabled = false;
			camera2.enabled = true;

			vanishExitButton();
		}
	}

	void appearExitButton(){
		GameObject.Find ("bt_scene_exit").transform.Translate(0, - 30, 0);
	}

	void vanishExitButton(){
		GameObject.Find ("bt_scene_exit").transform.Translate(0,   30, 0);
	}

	void freezePlayer(){
		gameObject.GetComponent<FirstPersonController>().enabled = false;
	}

	void playPlayer(){
		gameObject.GetComponent<FirstPersonController>().enabled = true;
	}
}